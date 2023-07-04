<?php

namespace Bengr\Support\Tests\Unit;

use Bengr\Support\CSVExporter;
use Bengr\Support\Tests\Support\TestResources\Models\User;
use Bengr\Support\Tests\TestCase;
use Carbon\Carbon;

class CSVExporterTest extends TestCase
{
    public function test_initializing_without_any_details()
    {
        $users = User::factory(10)->create();
        $exporter = CSVExporter::make($users, ['name']);

        $this->assertEquals($exporter->getColumns(), ['name']);
        $this->assertEquals($exporter->getRecords(), $users);
        $this->assertEquals($exporter->getFilename(), ((new Carbon())->format('m-d-Y-H-i-s') . '.csv'));
    }

    public function test_initializing_with_custom_fields()
    {
        $users = User::factory(10)->create();
        $users_second = User::factory(5)->create();
        $exporter = CSVExporter::make($users, ['name'])
            ->columns(['email'])
            ->records($users_second)
            ->filename('testing-filename.csv');

        $this->assertEquals($exporter->getColumns(), ['email']);
        $this->assertEquals($exporter->getRecords(), $users_second);
        $this->assertEquals($exporter->getFilename(), 'testing-filename.csv');
    }

    public function test_headers_contains_right_filename()
    {
        $users = User::factory(10)->create();
        $exporter = CSVExporter::make($users, ['name']);

        $filename = explode('=', array_map('trim', explode(';', $exporter->getHeaders()['Content-Disposition']))[1])[1];

        $this->assertEquals($filename, ((new Carbon())->format('m-d-Y-H-i-s') . '.csv'));
    }
}
