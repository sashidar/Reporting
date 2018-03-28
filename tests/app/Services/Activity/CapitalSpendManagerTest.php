<?php namespace Test\app\Services\Activity;

use App\Core\V201\Repositories\Activity\CapitalSpend;
use App\Core\Version;
use App\Models\Activity\Activity;
use App\Models\Organization\Organization;
use App\Services\Activity\CapitalSpendManager;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log as DbLogger;
use Psr\Log\LoggerInterface as Logger;
use Illuminate\Database\DatabaseManager;
use Mockery as m;
use Test\AidStreamTestCase;

/**
 * Class CapitalSpendManagerTest
 * @package Test\app\Services\Activity
 */
class CapitalSpendManagerTest extends AidStreamTestCase
{
    protected $version;
    protected $auth;
    protected $dbLogger;
    protected $logger;
    protected $capitalSpendRepo;
    protected $capitalSpendManager;
    protected $activity;
    protected $database;

    public function SetUp()
    {
        parent::setUp();
        $this->version          = m::mock(Version::class);
        $this->auth             = m::mock(Guard::class);
        $this->dbLogger         = m::mock(DbLogger::class);
        $this->logger           = m::mock(Logger::class);
        $this->capitalSpendRepo = m::mock(CapitalSpend::class);
        $this->activity         = m::mock(Activity::class);
        $this->database         = m::mock(DatabaseManager::class);
        $this->version->shouldReceive('getActivityElement->getCapitalSpend->getRepository')->andReturn(
            $this->capitalSpendRepo
        );
        $this->capitalSpendManager = new CapitalSpendManager(
            $this->version,
            $this->auth,
            $this->database,
            $this->dbLogger,
            $this->logger
        );
    }

    public function testItShouldUpdateActivityCapitalSpend()
    {
        $orgModel = m::mock(Organization::class);
        $orgModel->shouldReceive('getAttribute')->once()->with('name')->andReturn('orgName');
        $orgModel->shouldREceive('getAttribute')->once()->with('id')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getAttribute')->twice()->with('organization')->andReturn($orgModel);
        $this->auth->shouldReceive('user')->twice()->andReturn($user);
        $activityModel = $this->activity;
        $activityModel->shouldReceive('getAttribute')->with('id')->andreturn(1);
        $activityModel->shouldReceive('getAttribute')->once()->with('capital_spend')->andReturn(
            'testCapitalSpend'
        );
        $this->database->shouldReceive('beginTransaction')->once()->andReturnSelf();
        $this->capitalSpendRepo->shouldReceive('update')
                               ->once()
                               ->with(['capital_spend' => 'testCapitalSpend'], $activityModel)
                               ->andReturn(true);
        $this->database->shouldReceive('commit')->once()->andReturnSelf();
        $this->logger->shouldReceive('info')->once()->with(
            'Activity Capital Spend updated!',
            ['for' => 'testCapitalSpend']
        );
        $this->dbLogger->shouldReceive('activity')->once()->with(
            'activity.capital_spend',
            [
                'activity_id'     => 1,
                'organization'    => 'orgName',
                'organization_id' => 1
            ]
        );
        $this->assertTrue(
            $this->capitalSpendManager->update(
                ['capital_spend' => 'testCapitalSpend'],
                $activityModel
            )
        );
    }

    public function testItShouldGetCapitalSpendDataWithCertainId()
    {
        $this->capitalSpendRepo->shouldReceive('getCapitalSpendData')->once()->with(1)->andReturn(
            $this->activity
        );
        $this->assertInstanceOf(
            'App\Models\Activity\Activity',
            $this->capitalSpendManager->getCapitalSpendData(1)
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
