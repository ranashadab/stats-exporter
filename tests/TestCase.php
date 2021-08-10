<?php

namespace Booj\StatsExporter\Tests;

use Closure;
use Mockery;
use Mockery\MockInterface;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    /**
     * @var \App\Models\User $user
     */
    protected $user;
    protected $user_id;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /**
     * Factory built auth token for use in tests.
     * @param \App\Models\User $user
     * @return array
     */
    public function getAuthToken()
    {
        if (!$this->user) {
            $this->user = factory(\App\Models\User::class)->create();
            $this->user_id = $this->user->id;
        }
        $this->user_id = $this->user->id;

        $token = \App\Models\Auth\Builder::mint($this->user);
        return ['Authorization' => "Bearer $token"];
    }

    public function token($auth_id)
    {
        $user = \App\Models\User::query()->where('id', $auth_id)->first();
        $token = \App\Models\Auth\Builder::mint($user);
        return ['Authorization' => "Bearer $token"];
    }

    public function getServiceToken($id)
    {
        return (new \Lcobucci\JWT\Builder())
            ->issuedBy('https://service.booj.com')
            ->permittedFor('https://service.booj.com')
            ->identifiedBy(env('APP_KEY'), true)
            ->issuedAt(time())
            ->expiresAt(time() + 3600)
            ->withClaim('MCID', $id)
            ->getToken();
    }

    protected function writeFileToDataDir($file_name, $contents)
    {
        $file = fopen(base_path("tests/Data/$file_name"), "w");
        fwrite($file, $contents);
        fclose($file);
    }

    public function mockNotificationsNoteCreation()
    {
        $kit_mock = Mockery::mock(\Booj\NotificationsKit\NotificationsKit::class);
        $this->app->instance(\Booj\NotificationsKit\NotificationsKit::class, $kit_mock);
        $notifications_driver = Mockery::mock(\Booj\NotificationsKit\Drivers\Notifications\NotificationsDriver::class);
        $model_mock = Mockery::mock(\Booj\BDK\DataAccess\Model::class);
        $topics_driver = Mockery::mock(\Booj\NotificationsKit\Drivers\TopicManagement\TopicManagementDriver::class);
        $user_topics_driver = Mockery::mock(\Booj\NotificationsKit\Drivers\TopicManagement\UserTopicManagement\UserTopicManagementDriver::class);
        $topics_driver->shouldReceive('createTopic')->once()->andReturnSelf();
        $topics_driver->shouldReceive('model')->once()->andReturn($model_mock);
        $kit_mock->shouldReceive('get')->once()->with('topics')->andReturn($topics_driver);
        $kit_mock->shouldReceive('get')->once()->with('user_topics')->andReturn($user_topics_driver);
        $kit_mock->shouldReceive('get')->once()->with('notifications')->andReturn($notifications_driver);
        $model_mock->shouldReceive('get')->once()->with('id')->andReturn('abc');
        $user_topics_driver->shouldReceive('subscribeUserToTopic');
        $notifications_driver->shouldReceive('sendToTopic')->once();
    }

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function instance($abstract, $instance)
    {
        $this->app->instance($abstract, $instance);

        return $instance;
    }

    /**
     * Mock an instance of an object in the container.
     *
     * @param string $abstract
     * @param Closure|null $mock
     * @return MockInterface
     */
    protected function mock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }

    /**
     * Mock a partial instance of an object in the container.
     *
     * @param string $abstract
     * @param Closure|null $mock
     * @return MockInterface
     */
    protected function partialMock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    /**
     * @param $name
     * @return false|string
     */
    protected function getJsonMessage($name)
    {
        return file_get_contents(app()->basePath() . '/tests/Fixtures/json-responses/' . $name);
    }
}