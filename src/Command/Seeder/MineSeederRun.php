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

namespace Mine\Command\Seeder;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Concerns\Confirmable;
use Hyperf\Database\Commands\Seeders\BaseCommand;
use Hyperf\Database\Seeders\Seed;
use Mine\Kernel\Tenant\Tenant;
use Mine\Mapper\TenantMapper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MineSeederRun
 * @package System\Command\Seeder
 */
#[Command]
class MineSeederRun extends BaseCommand
{
    use Confirmable;

    /**
     * The console command name.
     *
     * @var string|null
     */
    protected ?string $name = 'mine:seeder-run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Seed the database with records';

    /**
     * The seed instance.
     *
     * @var Seed
     */
    protected Seed $seed;

    protected string $module;
    protected TenantMapper $tenantMapper;

    /**
     * Create a new seed command instance.
     * @param Seed $seed
     * @param TenantMapper $tenantMapper
     */
    public function __construct(Seed $seed, TenantMapper $tenantMapper)
    {
        parent::__construct();

        $this->seed = $seed;
        $this->tenantMapper = $tenantMapper;

        $this->setDescription('The run seeder class of MineAdmin module');
    }

    /**
     * Handle the current command.
     */
    public function handle(): void
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->module = ucfirst(trim($this->input->getArgument('name')));
        $tenant = $this->input->getArgument('tenant');

        if ($tenant) {
            //执行指定租户
            $this->exec($tenant);
        } else {
            // 获取所有租户ID
            $tenantList = $this->tenantMapper->getListForCursor(['select' => ['corp_code']]);
            // 执行
            foreach ($tenantList as $tenant) {
                $this->exec($tenant->tenant_id);
            }
        }
    }

    protected function exec(string $poolName = 'default')
    {
        Tenant::instance()->init($poolName);

        $this->seed->setOutput($this->output);

        $this->seed->setConnection($poolName);

        $this->seed->run([$this->getSeederPath()]);
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The run seeder class of the name'],
            ['tenant', InputArgument::OPTIONAL, 'The run seeder class of the init', ''],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the seeders file stored'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided seeder file paths are pre-resolved absolute paths'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }


    protected function getSeederPath(): string
    {
        if (!is_null($targetPath = $this->input->getOption('path'))) {
            return !$this->usingRealPath()
                ? BASE_PATH . '/' . $targetPath
                : $targetPath;
        }

        return BASE_PATH . '/app/' . $this->module . '/Database/Seeders';
    }
}
