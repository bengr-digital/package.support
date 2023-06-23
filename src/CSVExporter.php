<?php

namespace Bengr\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;

class CSVExporter
{
    protected ?Collection $records = null;

    protected array $columns = [];

    protected ?string $filename = null;

    final public function __construct(Collection $records, array $columns)
    {
        $this->records($records);
        $this->columns($columns);
    }

    public static function make(Collection $records, array $columns): static
    {
        return app(static::class, [
            'records' => $records,
            'columns' => $columns
        ]);
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function records(Collection $records): self
    {
        $this->records = $records;

        return $this;
    }

    public function filename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function stream()
    {
        $filename = $this->getFilename();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Access-Control-Expose-Headers" => "Content-Disposition",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, $this->getColumns());

            foreach ($this->getRecords() as $record) {
                $row = [];

                foreach ($this->getColumns() as $column) {
                    array_push($row, $record->$column);
                }


                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function getFilename(): string
    {
        return $this->filename ?? ((new Carbon())->format('m-d-Y-H-i-s') . '.csv');
    }
}
