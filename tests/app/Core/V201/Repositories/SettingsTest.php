<?php namespace Test\app\Core\V201\Repositories;

use App\Core\V201\Repositories\SettingsRepository;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationData;
use App\Models\Settings;
use App\User;
use Test\AidStreamTestCase;
use Illuminate\Session\SessionManager;
use Illuminate\Database\DatabaseManager;
use Mockery as m;

class SettingsTest extends AidStreamTestCase
{
    protected $settings;
    protected $settingRepository;
    protected $organizationData;
    protected $sessionManager;
    protected $databaseManager;
    protected $organization;

    public function setup()
    {
        parent::setUp();
        $this->settings          = m::mock(Settings::class);
        $this->organizationData  = m::mock(OrganizationData::class);
        $this->organization      = m::mock(Organization::class);
        $this->sessionManager    = m::mock(SessionManager::class);
        $this->databaseManager   = m::mock(DatabaseManager::class);
        $this->user              = m::mock(User::class);
        $this->settingRepository = new SettingsRepository($this->settings, $this->organizationData, $this->sessionManager, $this->databaseManager, $this->user);
    }

    /**
     * @test
     */
    public function testItShouldReturnSettingsDataWithSpecificOrganizationId()
    {
        $this->settings->shouldReceive('where->first')->andReturn($this->settings);
        $this->assertInstanceOf('App\Models\Settings', $this->settingRepository->getSettings(1));
    }

    /**
     * @test
     */
    public function testItShouldStoreSettings()
    {
        $this->organization->shouldReceive('organization->save')->with(['reporting_org' => 1]);
        $this->settings->shouldReceive('settings->create');
        $this->organizationData->shouldReceive('create')->with('organization_id');
        $this->assertTrue(true, 'Organization Settings Inserted');
    }

    /**
     * @test
     */
    public function testItShouldUpdateSettings()
    {
        $this->organization->shouldReceive('organization->save')->with(['reporting_org' => 1]);
        $this->settings->shouldReceive('settings->create');
        $this->organizationData->shouldReceive('firstOrCreate')->with('organization_id');
        $this->assertTrue(true, 'Organization Settings Updated');
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
