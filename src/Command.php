<?php

namespace Larapack\SparkDiff;

use Illuminate\Console\Command as IlluminateCommand;

class Command extends IlluminateCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spark:diff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an differences between your app and the Spark repository.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->handleViews();
    }

    /**
     * Check diff for views between application and Spark files.
     *
     * @return void
     */
    protected function handleViews()
    {
        $path = base_path();

        exec("diff -qrs {$path}/resources/views/vendor/spark/ {$path}/spark/resources/views/", $output, $status);

        if ($status != 1) {
            $this->error('Something went wrong!');
            exit;
        }

        foreach ($output AS $line)
        {
            $appFile = $this->getAppFile($line);
            $sparkFile = $this->getSparkFile($line);
            $isDiffer = $this->isDiffer($line);

            if ($isDiffer) {
                $this->comment('');
                $this->comment(' * ' . substr($appFile, strlen($path) + 1) . ' modified!');
                exec("diff $appFile $sparkFile", $singleOutput);
                foreach ($singleOutput AS $row) {
                    $this->info($row);
                }
            }
        }

        $this->comment("");
        $this->comment("Done");
    }

    /**
     * Get application file from line.
     *
     * @param $line
     * @return string
     */
    protected function getAppFile($line)
    {
        return explode(' and', explode('Files ', $line)[1])[0];
    }

    /**
     * Get Spark file from line.
     *
     * @param $line
     * @return string
     */
    protected function getSparkFile($line)
    {
        if ($this->isDiffer($line)) {
            return explode(' ', explode(' and', $line)[1])[1];
        }

        return explode(' are', explode(' and', $line)[1])[0];
    }

    /**
     * Check if line is differ.
     *
     * @param $line
     * @return bool
     */
    protected function isDiffer($line)
    {
        return last(explode(' ', $line)) == "differ";
    }
}
