<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Utility;

use Symfony\Component\Process\Exception\ProcessTimedOutException;

class ParallelGroup
{

    protected $processes = [];

    protected $timeouts = [];

    public function __construct(array $processes)
    {
        $this->processes = $processes;
    }

    public function run()
    {
        foreach ($this->processes as $process) {
            $process->start();
        }
        usleep(1000);
        while ($this->stillRunning()) {
            usleep(1000);
        }
    }

    public function stillRunning()
    {
        foreach ($this->processes as $index => $process) {
            try {
                $process->checkTimeout();
            } catch (ProcessTimedOutException $e) {
                $this->timeouts[] = $index;
            }
            if ($process->isRunning()) {
                return true;
            }
        }
    }

    public function timedOut($index)
    {
        return in_array($index, $this->timeouts);
    }

    public function reset()
    {
        $this->processes = [];
    }

}