<?php //-->

//activated by: generate product

namespace Eve\Framework\Cli;

class Help extends \Eve\Framework\Base
{
    /**
     * @var string|null $cwd The path from where this was called
     */
    protected $cwd = null;

    /**
     * We need the CWD
     *
     * @param string $cwd The path from where this was called
     */
    public function __construct($cwd)
    {
        $this->cwd = $cwd;
    }

    /**
     * Runs the CLI process
     *
     * @param array $args CLI arguments
     *
     * @return mixed
     */
    public function run(array $args)
    {
        Index::info('Help Menu');
        Index::info('- `eve generate <schema> <namespace>`     Generates files based on schema');
        Index::info('- `eve database <schema>`                 Generates database table/s schema');
        Index::info('- `eve install`                           Generates default framework files');

        die(0);
    }
}