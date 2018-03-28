<?php namespace Test\app\Services\Activity;

use App\Core\V201\Repositories\Activity\Result;
use App\Core\Version;
use App\Models\Activity\ActivityResult;
use App\Models\Organization\Organization;
use App\Services\Activity\ResultManager;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log as DbLogger;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface as Logger;
use Illuminate\Database\DatabaseManager;
use Mockery as m;
use Test\AidStreamTestCase;

/**
 * Class ResultManagerTest
 * @package Test\app\Services\Activity
 */
class ResultManagerTest extends AidStreamTestCase
{
    protected $version;
    protected $auth;
    protected $dbLogger;
    protected $logger;
    protected $database;
    protected $resultRepo;
    protected $resultManager;
    protected $activityResult;

    public function SetUp()
    {
        parent::setUp();
        $this->version        = m::mock(Version::class);
        $this->auth           = m::mock(Guard::class);
        $this->dbLogger       = m::mock(DbLogger::class);
        $this->logger         = m::mock(Logger::class);
        $this->database       = m::mock(DatabaseManager::class);
        $this->resultRepo     = m::mock(Result::class);
        $this->activityResult = m::mock(ActivityResult::class);
        $this->version->shouldReceive('getActivityElement->getResult->getRepository')->andReturn($this->resultRepo);
        $this->resultManager = new ResultManager(
            $this->version,
            $this->auth,
            $this->database,
            $this->dbLogger,
            $this->logger
        );
    }

    /**
     * @test
     */
    public function testItShouldUpdateActivityResult()
    {
        $this->database->shouldReceive('beginTransaction');
        $orgModel = m::mock(Organization::class);
        $orgModel->shouldReceive('getAttribute')->once()->with('name')->andReturn('orgName');
        $orgModel->shouldReceive('getAttribute')->once()->with('id')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getAttribute')->twice()->with('organization')->andReturn($orgModel);
        $this->auth->shouldReceive('user')->twice()->andReturn($user);
        $this->resultRepo->shouldReceive('update')->with(['result' => 'testResult'], $this->activityResult);
        $this->activityResult->shouldReceive('getAttribute')->with('result')->andReturn('testResult');
        $this->activityResult->shouldReceive('getAttribute')->with('activity_id')->andReturn(1);
        $this->database->shouldReceive('commit');
        $this->logger->shouldReceive('info')->with('Activity Result saved!', ['for' => 'testResult']);
        $this->dbLogger->shouldReceive('activity')->with(
            'activity.result_saved',
            [
                'activity_id'     => 1,
                'organization'    => 'orgName',
                'organization_id' => 1
            ]
        );
        $this->assertTrue($this->resultManager->update(['result' => 'testResult'], $this->activityResult));
    }

    /**
     * @test
     */
    public function testItShouldGetResultsWithCertainActivityId()
    {
        $collection = m::mock(Collection::class);
        $this->resultRepo->shouldReceive('getResults')->with(1)->andReturn($collection);
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $this->resultManager->getResults(1)
        );
    }

    /**
     * @test
     */
    public function testItShouldGetResultWithCertainId()
    {
        $this->resultRepo->shouldReceive('getResult')->with(1, 1)->andReturn($this->activityResult);
        $this->assertInstanceOf(
            'App\Models\Activity\ActivityResult',
            $this->resultManager->getResult(1, 1)
        );
    }

    /**
     * @test
     */
    public function testItShouldReceiveDeleteResult()
    {
        $this->resultRepo->shouldReceive('deleteResult')->with($this->activityResult)->andReturn(true);
        $this->activityResult->shouldReceive('toArray')->once()->andReturn([]);
        $this->activityResult->shouldReceive('getAttribute')->once()->with('id')->andReturn(1);
        $this->activityResult->shouldReceive('getAttribute')->once()->with('activity_id')->andReturn(2);
        $this->dbLogger->shouldReceive('activity')->with(
            "activity.activity_result_deleted",
            [
                'result_id'   => 1,
                'activity_id' => 2
            ],
            []
        );
        $this->assertTrue($this->resultManager->deleteResult($this->activityResult));
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
