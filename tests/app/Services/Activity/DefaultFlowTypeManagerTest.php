<?php namespace Test\app\Services\Activity;

use App\Core\V201\Repositories\Activity\DefaultFlowType;
use App\Core\Version;
use App\Models\Activity\Activity;
use App\Models\Organization\Organization;
use App\Services\Activity\DefaultFlowTypeManager;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log as DbLogger;
use Psr\Log\LoggerInterface as Logger;
use Illuminate\Database\DatabaseManager;
use Mockery as m;
use Test\AidStreamTestCase;

/**
 * Class DefaultFlowTypeManagerTest
 * @package Test\app\Services\Activity
 */
class DefaultFlowTypeManagerTest extends AidStreamTestCase
{
    protected $version;
    protected $auth;
    protected $dbLogger;
    protected $logger;
    protected $defaultFlowTypeRepo;
    protected $defaultFlowTypeManager;
    protected $activity;
    protected $database;

    public function SetUp()
    {
        parent::setUp();
        $this->version             = m::mock(Version::class);
        $this->auth                = m::mock(Guard::class);
        $this->dbLogger            = m::mock(DbLogger::class);
        $this->logger              = m::mock(Logger::class);
        $this->defaultFlowTypeRepo = m::mock(DefaultFlowType::class);
        $this->activity            = m::mock(Activity::class);
        $this->database            = m::mock(DatabaseManager::class);
        $this->version->shouldReceive('getActivityElement->getDefaultFlowType->getRepository')->andReturn(
            $this->defaultFlowTypeRepo
        );
        $this->defaultFlowTypeManager = new DefaultFlowTypeManager(
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
    public function testItShouldUpdateActivityDefaultFlowType()
    {
        $orgModel = m::mock(Organization::class);
        $orgModel->shouldReceive('getAttribute')->once()->with('name')->andReturn('orgName');
        $orgModel->shouldREceive('getAttribute')->once()->with('id')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getAttribute')->twice()->with('organization')->andReturn($orgModel);
        $this->auth->shouldReceive('user')->twice()->andReturn($user);
        $activityModel = $this->activity;
        $activityModel->shouldReceive('getAttribute')->with('id')->andreturn(1);
        $activityModel->shouldReceive('getAttribute')->once()->with('default_flow_type')->andReturn(
            'testDefaultFlowType'
        );
        $this->database->shouldReceive('beginTransaction')->once()->andReturnSelf();
        $this->defaultFlowTypeRepo->shouldReceive('update')
                                  ->once()
                                  ->with(['default_flow_type' => 'testDefaultFlowType'], $activityModel)
                                  ->andReturn(true);
        $this->database->shouldReceive('commit')->once()->andReturnSelf();
        $this->logger->shouldReceive('info')->once()->with(
            'Activity Default Flow Type updated!',
            ['for' => 'testDefaultFlowType']
        );
        $this->dbLogger->shouldReceive('activity')->once()->with(
            'activity.default_flow_type',
            [
                'activity_id'     => 1,
                'organization'    => 'orgName',
                'organization_id' => 1
            ]
        );
        $this->assertTrue(
            $this->defaultFlowTypeManager->update(
                ['default_flow_type' => 'testDefaultFlowType'],
                $activityModel
            )
        );
    }

    /**
     * @test
     */
    public function testItShouldGetDefaultFlowTypeDataWithCertainId()
    {
        $this->defaultFlowTypeRepo->shouldReceive('getDefaultFlowTypeData')->once()->with(1)->andReturn(
            $this->activity
        );
        $this->assertInstanceOf(
            'App\Models\Activity\Activity',
            $this->defaultFlowTypeManager->getDefaultFlowTypeData(1)
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
