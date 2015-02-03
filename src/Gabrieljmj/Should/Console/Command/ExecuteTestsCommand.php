<?php
/**
 * ShouldPHP
 *
 * @author  Gabriel Jacinto <gamjj74@hotmail.com>
 * @status  dev
 * @link    https://github.com/GabrielJMJ/ShouldPHP
 * @license MIT
 */
 
namespace Gabrieljmj\Should\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Gabrieljmj\Should\Ambient\AmbientInterface;
use Gabrieljmj\Should\Template\TemplateInterface;
use Gabrieljmj\Should\Report\Report;
use Gabrieljmj\Should\Event\ExecutionEventInterface;
use Gabrieljmj\Should\Logger\LoggerAdapterInterface;

class ExecuteTestsCommand extends Command
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \Gabrieljmj\Should\Report\Report
     */
    private $report;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Symfony\Component\EventDispatcher\Event
     */
    private $event;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \Symfony\Component\EventDispatcher\Event $event
     */
    public function setEvent(ExecutionEventInterface $event)
    {
        $this->event = $event;
    }

    /**
     * @param \Gabrieljmj\Should\Logger\LoggerAdapterInterface $logger
     */
    public function setLogger(LoggerAdapterInterface $logger)
    {
        if (!$logger instanceof LoggerInterface) {
            throw new \Exception('Logger should impements Psr\Log\LoggerInterface');
        }

        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setName('execute')
             ->setDescription('Executes tests')
             ->addArgument(
                    'file',
                    InputArgument::REQUIRED,
                    'Indicate the file or the path that contains the tests ambients'
             )
             ->addOption(
                    'save',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'Do you want to save the report?'
             );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $file = $this->input->getArgument('file');
        $this->input->hasArgument('test_name') ? $testName = $this->input->getArgument('test_name') : null;

        if ($this->isDir($file)) {
            $dir = $file;
            $ambientFiles = $this->getAmbientFilesFromDir($dir);

            $this->executeArrayOfTests($ambientFiles);
        } else {
            $ext = $this->getFileExt($file);

            if ($ext === 'php') {
                $ambient = $this->validateFile($file);

                $this->runTest($ambient);
            } elseif ($ext === 'json') {
                if (file_exists($file)) {
                    $fileContent = file_get_contents($file);
                    $json = json_decode($fileContent);
                    $ambientFiles = $json->ambients;

                    $this->executeArrayOfTests($ambientFiles);
                }
            } elseif (class_exists(str_replace('/', '\\', $file))) {
                $file = str_replace('/', '\\', $file);
                $this->executeAmbientObject($file);
            }
        }

        $this->event->setOutput($output);
        $this->event->setReport($this->report);

        $this->eventDispatcher->dispatch('should.execute', $this->event);
    }

    /**
     * @param string $file
     * @return string
     */
    private function getFileExt($file)
    {
        $e = explode('.', $file);
        return end($e);
    }

    /**
     * @param string $file
     * @return \Gabrieljmj\Should\AmbientInterface
     */
    private function validateFile($file)
    {
        if (!file_exists($file)) {
            throw new \Exception('The file of an ambient was not found: ' . $file);
        }

        $ambientInstance = require $file;

        if (!is_object($ambientInstance) || !$ambientInstance instanceof AmbientInterface) {
            throw new \Exception('The file of an ambient does not return an instance of a valid ambient');
        }

        return $ambientInstance;
    }

    /**
     * @param \Gabrieljmj\Should\AmbientInterface $ambient
     * @return \Gabrieljmj\Should\Collection
     */
    private function runTest(AmbientInterface $ambient)
    {
        $ambient->run();
        $report = $ambient->getReport();

        if ($this->input->getOption('save')) {
            $this->logger->setFile($this->input->getOption('save'));
            $this->logger->info($report->serialize());
        }

        if ($this->report === null) {
            $this->report = new Report('all_tested');
        }

        $assertList = $report->getAssertList();

        foreach ($assertList as $testType => $value) {
            if (isset($assertList[$testType]['fail'])) {
                foreach ($assertList[$testType]['fail'] as $element => $fails) {
                    foreach ($fails as $fail) {
                        $this->report->addAssert($fail);
                    }
                }
            }

            if (isset($assertList[$testType]['success'])) {
                foreach ($assertList[$testType]['success'] as $element => $successes) {
                    foreach ($successes as $success) {
                        $this->report->addAssert($success);
                    }
                }
            }
        }
    }

    /**
     * @param array $ambientFiles
     * @return array
     */
    private function executeArrayOfTests(array $ambientFiles)
    {
        $ambients = [];

        foreach ($ambientFiles as $ambientFile) {
            if ($this->isDir($ambientFile)) {
                $this->executeArrayOfTests($this->getAmbientFilesFromDir($ambientFile));
            } elseif (class_exists($ambientFile)) {
                $this->executeAmbientObject($ambientFile);
            } else {
                $ambients[] = $this->validateFile($ambientFile);
            }
        }

        foreach ($ambients as $ambient) {
            $this->runTest($ambient);
        }
    }

    /**
     * @param string $dir
     * @return boolean
     */
    private function isDir($dir)
    {
        return substr($dir, -1) == '/';
    }

    /**
     * @param string $dir
     * @return array
     */
    private function getAmbientFilesFromDir($dir)
    {   
        if (!is_dir($dir) || !is_readable($dir)) {
            throw new Exception('Directory not exists or is not readable: ' . $dir);
        }

        $files = scandir($dir);
        unset($files[0], $files[1]);

        foreach ($files as $key => $file) {
            $files[$key] = $dir . DIRECTORY_SEPARATOR . $file;
        }

        return $files;
    }

    /**
     * @param string $class
     * @return array
     */
    private function executeAmbientObject($class)
    {
        $ref = new \ReflectionClass($class);
        $methods = $ref->getMethods();
        $instance = $ref->newInstance();
        $executedMethods = 0;

        if (!$instance instanceof AmbientInterface) {
            throw new \Exception('This ambient is not a directory or a file or a object: ' . $class);
        }

        foreach ($methods as $method) {
            if ($method->isPublic()) {
                if (strtolower(substr($method->name, 0, 4)) === 'test') {
                    $executedMethods++;
                    call_user_func_array([$instance, $method->name], []);
                }
            }
        }

        return $this->runTest($instance);
    }
}