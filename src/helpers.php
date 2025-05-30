<?php

use Glama\Glama;
use Glama\Process;

function glama(): Glama
{
    return new Glama;
}

/**
 * @param  array<string>  $commands
 * @return bool
 */
function glama_process_run(array $commands): Process
{
    return Process::run($commands);
}

/**
 * @param  $process
 */
function glama_is_ollama_server_up(): bool
{
    return Process::isRunningOnBG('ollama serve');
}
