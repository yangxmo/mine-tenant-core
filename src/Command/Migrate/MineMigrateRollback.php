<?php
/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://gitee.com/xmo/MineAdmin
 */

declare(strict_types=1);
namespace Mine\Command\Migrate;

use Hyperf\Command\Concerns\Confirmable;
use Hyperf\Database\Commands\Migrations\BaseCommand;
use Hyperf\Database\Migrations\Migrator;
use Mine\Kernel\Tenant\Tenant;
use Mine\Mapper\TenantMapper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Hyperf\Command\Annotation\Command;

/**
 * Class MineMigrateRollback
 * @package System\Command\Migrate
 */
#[Command]
class MineMigrateRollback extends BaseCommand
{
    use Confirmable;

    protected ?string $name = 'mine:migrate-rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Run rollback the database migrations';

    /**
     * The migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    protected $module;
    protected TenantMapper $tenantMapper;

    /**
     * Create a new migration command instance.
     * @param Migrator $migrator
     * @param TenantMapper $tenantMapper
     */
    public function __construct(Migrator $migrator, TenantMapper $tenantMapper)
    {
        parent::__construct();

        $this->migrator = $migrator;

        $this->tenantMapper = $tenantMapper;

        $this->setDescription('The run migrate rollback class of MineAdmin module');
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->module = trim($this->input->getArgument('name'));
        $tenant = $this->input->getArgument('tenant');

        if ($tenant) {
            //执行指定租户
            $this->exec($tenant);
        } else {
            // 获取所有租户ID
            $tenantList = $this->tenantMapper->getListForCursor(['select' => ['corp_code']]);

            // 执行
            foreach ( $tenantList as $tenant) {
                $this->exec($tenant->tenant_id);
            }
        }
    }

    protected function exec (string $poolName = 'default'): void
    {
        Tenant::instance()->init($poolName);

        $this->prepareDatabase($poolName);

        // Next, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        $this->migrator->setOutput($this->output)
            ->rollback($this->getMigrationPaths(), [
                'pretend' => $this->input->getOption('pretend'),
                'step' => $this->input->getOption('step'),
            ]);

        // Finally, if the "seed" option has been given, we will re-run the database
        // seed task to re-populate the database, which is convenient when adding
        // a migration and a seed at the same time, as it is only this command.
        if ($this->input->getOption('seed') && !$this->input->getOption('pretend')) {
            $this->call('db:seed', ['--force' => true]);
        }
    }

    protected function getOptions(): array
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to be executed'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
            ['step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually'],
        ];
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase(string $poolName)
    {
        $this->migrator->setConnection($poolName);
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'Please enter the module to be run'],
            ['tenant', InputArgument::OPTIONAL, 'Please enter the init to be init', ''],
        ];
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath(): string
    {
        return BASE_PATH . '/app/' . ucfirst($this->module) . '/Database/Migrations';
    }
}
