<?php

namespace Tests\Integration;

use App\Events\Invoice\InvoiceWasUpdated;
use App\Models\Activity;
use App\Repositories\ActivityRepository;
use App\Utils\Ninja;
use App\Utils\Traits\MakesHash;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\MockAccountData;
use Tests\TestCase;

/**
 * @test
 * @covers App\Http\Controllers\ActivityController
 */
class DownloadHistoricalInvoiceTest extends TestCase
{
    use MockAccountData;
    use DatabaseTransactions;
    use MakesHash;

    public function setUp() :void
    {
        parent::setUp();

        $this->makeTestData();
    }

    private function mockActivity()
    {
        $activity_repo = new ActivityRepository();

        $obj = new \stdClass;
        $obj->invoice_id = $this->invoice->id;
        $obj->user_id = $this->invoice->user_id;
        $obj->company_id = $this->company->id;

        $activity_repo->save($obj, $this->invoice, Ninja::eventVars());

    }
    public function testActivityAccessible()
    {
        $this->mockActivity();

        $this->assertNotNull($this->invoice->activities);
    }

    public function testBackupExists()
    {
        $this->mockActivity();

        $this->assertNotNull($this->invoice->activities->first()->backup->html_backup);
    }

    public function testBackupDownload()
    {
        $this->mockActivity();

        $response = $this->withHeaders([
                'X-API-SECRET' => config('ninja.api_secret'),
                'X-API-TOKEN' => $this->token
            ])->get('/api/v1/activities/download_entity/'.$this->encodePrimaryKey($this->invoice->activities->first()->id));

        $response->assertStatus(200);
    }

    public function testBackupCheckPriorToDownloadWorks()
    {
        $this->mockActivity();

        $backup = $this->invoice->activities->first()->backup;
        $backup->forceDelete();

        $response = $this->withHeaders([
                'X-API-SECRET' => config('ninja.api_secret'),
                'X-API-TOKEN' => $this->token
            ])->get('/api/v1/activities/download_entity/'.$this->encodePrimaryKey($this->invoice->activities->first()->id));

        $response->assertStatus(404);
    }
}