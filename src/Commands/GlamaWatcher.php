<?php

namespace Glama\Commands;

use Glama\Process;
use Illuminate\Console\Command;

class GlamaWatcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'glama:provider-watch';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Glama Watch process and logs it';
    public function handle()
    {
        $this->info("Ollama Watching...");
        while(true) {
            $processes = Process::getOllamaProcesses();
            if(!empty($processes)) {
                $this->output->write(chr(27)."[2J".chr(27)."[H");
                $this->info("Last updated: " . date('Y-m-d H:i:s'));
                $this->table(
                    ['NAME', 'ID', 'SIZE', 'PROCESSOR', 'UNTIL'],
                    array_map(
                        function ($process) {
                            return [
                                $process['NAME'],
                                $process['ID'],
                                $process['SIZE'],
                                $process['PROCESSOR'],
                                $process['UNTIL']
                            ];
                        }, $processes
                    )
                );
                sleep(5);
            } else {
                $this->info("No Ollama processes running. Checking again in 5 seconds...");
                sleep(5);
            }
        }
    }
}
