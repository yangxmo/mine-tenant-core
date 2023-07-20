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

namespace Mine\Command;

use Exception;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Mine\MineCommand;
use Mine\Mine;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class InstallProjectCommand
 * @package System\Command
 */
#[Command]
class InstallProjectCommand extends MineCommand
{
    /**
     * 安装命令
     * @var string|null
     */
    protected ?string $name = 'mine:install';

    protected const CONSOLE_GREEN_BEGIN = "\033[32;5;1m";
    protected const CONSOLE_RED_BEGIN = "\033[31;5;1m";
    protected const CONSOLE_END = "\033[0m";

    protected array $database = [];

    protected array $redis = [];

    protected string $tenant = '';


    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php mine:install" install MineAdmin system');
        $this->setDescription('MineAdmin system install command');

        $this->addOption('option', '-o', InputOption::VALUE_OPTIONAL, 'input "-o reset" is re install MineAdmin');
    }

    public function handle()
    {
        // 获取参数
        $option = $this->input->getOption('option');

        // 全新安装
        if ($option === null) {

            if (!file_exists(BASE_PATH . '/.env')) {
                // 欢迎
                $this->welcome();

                // 检测环境
                $this->checkEnv();

                // 设置租户
                $this->tenantId();

                // 设置env
                $this->generatorEnvFile();

                $this->line("\n\nReset the \".env\" file. Please restart the service before running \nthe installation command to continue the installation.", "info");
            } else if (file_exists(BASE_PATH . '/.env') && $this->confirm('Do you want to continue with the installation program?', true)) {

                // 设置租户
                $this->tenantId();

                // 安装本地模块
                $this->installLocalModule();

                // 其他设置
                $this->setOthers();

                // 设置env
                $this->generatorEnvFile();

                // 安装完成
                $this->finish();
            } else {

                // 欢迎
                $this->welcome();

                // 检测环境
                $this->checkEnv();

                // 设置租户
                $this->tenantId();

                // 安装本地模块
                $this->installLocalModule();

                // 其他设置
                $this->setOthers();

                // 设置env
                $this->generatorEnvFile();

                // 安装完成
                $this->finish();
            }
        }

        // 重新安装
        if ($option === 'reset') {
            $this->line('Reinstallation is not complete...', 'error');
        }
    }

    protected function welcome()
    {
        $this->line('-----------------------------------------------------------', 'comment');
        $this->line('Hello, welcome use MineAdmin system.', 'comment');
        $this->line('The installation is about to start, just a few steps', 'comment');
        $this->line('-----------------------------------------------------------', 'comment');
    }

    protected function checkEnv()
    {
        $answer = $this->confirm('Do you want to test the system environment now?', true);

        if ($answer) {

            $this->line(PHP_EOL . ' Checking environmenting...' . PHP_EOL, 'comment');

            if (version_compare(PHP_VERSION, '8.0', '<')) {
                $this->error(sprintf(' php version should >= 8.0 >>> %sNO!%s', self::CONSOLE_RED_BEGIN, self::CONSOLE_END));
                exit;
            }
            $this->line(sprintf(" php version %s >>> %sOK!%s", PHP_VERSION, self::CONSOLE_GREEN_BEGIN, self::CONSOLE_END));

            $extensions = ['swoole', 'mbstring', 'json', 'openssl', 'pdo', 'xml'];

            foreach ($extensions as $ext) {
                $this->checkExtension($ext);
            }
        }
    }

    /**
     * install modules
     */
    protected function installLocalModule(): void
    {
        /* @var Mine $mine */
        $this->line("Installation of local modules is about to begin...\n", 'comment');
        $mine = make(Mine::class);
        $modules = $mine->getModuleInfo();
        foreach ($modules as $name => $info) {
            $this->call('mine:migrate-run', ['name' => $name, '--force' => 'true', 'tenant' => $this->tenant]);
            if ($name === 'System') {
                $this->initUserData();
            }
            $this->call('mine:seeder-run',  ['name' => $name, '--force' => 'true', 'tenant' => $this->tenant]);
            $this->line($this->getGreenText(sprintf('"%s" module install successfully', $name)));
        }
    }

    protected function tenantId(): void
    {
        $answer = $this->ask('Set you tenantId to init the system', '');

        if ($answer) {
            $this->tenant = $answer;
        } else {
            $this->error('TenantId is not Set');
            exit;
        }
    }

    /**
     * @throws Exception
     */
    protected function generatorEnvFile(): void
    {
        try {
            $envContent = file_get_contents(BASE_PATH . '/.env.example');

            if ($envContent) {

                $env = file_get_contents(BASE_PATH . '/.env');

                if(!$env) {
                    file_put_contents(BASE_PATH . '/.env', $envContent);
                }

                $this->line($this->getGreenText(sprintf(' env created successfully')));
            } else {
                $this->line($this->getRedText(sprintf('Failed to create database "%s". Please create it manually', $this->tenant)));
            }
        } catch (\Exception $e) {
            $this->line($this->getRedText($e->getMessage()));
            exit;
        }
    }

    protected function setOthers(): void
    {
        $this->line(PHP_EOL . ' MineAdmin set others items...' . PHP_EOL, 'comment');
        $this->call('mine:update', ['tenant' => $this->tenant]);

        if (! file_exists(BASE_PATH . '/config/autoload/mineadmin.php')) {
            $this->call('vendor:publish', [ 'package' => 'xmo/mine' ]);
        }
    }

    protected function initUserData(): void
    {
        // 清理数据
        $db = Db::connection($this->tenant);

        $db->table('system_user')->truncate();
        $db->table('system_role')->truncate();
        $db->table('system_user_role')->truncate();
        if (\Hyperf\Database\Schema\Schema::hasTable('system_user_dept')) {
            $db->table('system_user_dept')->truncate();
        }

        $account = $this->ask('set your account for login', '16858888988');
        $password = $this->ask('set your password for login', 'admin123');

        // 创建超级管理员
        $db->table('system_user')->insert([
            'id' => env('SUPER_ADMIN', 1),
            'username' => 'superAdmin',
            'corp_code' => $this->tenant == 'tourist' ? $this->tenant : '',
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_type' => '100',
            'nickname' => '创始人',
            'email' => 'admin@adminmine.com',
            'phone' => $account,
            'signed' => '广阔天地，大有所为',
            'dashboard' => 'statistics',
            'created_by' => 0,
            'updated_by' => 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        // 创建管理员角色
        $db->table('system_role')->insert([
            'id' => env('ADMIN_ROLE', 1),
            'name' => '超级管理员（创始人）',
            'code' => 'superAdmin',
            'data_scope' => 0,
            'sort' => 0,
            'created_by' => env('SUPER_ADMIN', 0),
            'updated_by' => 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'remark' => '系统内置角色，不可删除',
        ]);
        // 初始化默认角色
        $db->table('system_user_role')->insert([
            'user_id' => env('SUPER_ADMIN', 1),
            'role_id' => env('ADMIN_ROLE', 1),
        ]);
        // 初始化企业账户
        $db->table('account')->insert([
            'money' => 0.00,
            'freeze_money' => 0.00,
            'pay_money' => 0.00,
        ]);

        if ($this->tenant == 'tourist') {
            // 创建游客角色
            $db->table('system_role')->insert([
                'id' => 2,
                'name' => '游客',
                'code' => 'tourist',
                'data_scope' => 0,
                'sort' => 0,
                'created_by' => 0,
                'updated_by' => 0,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'remark' => '系统内置角色，不可删除',
            ]);
        }
    }

    protected function finish(): void
    {
        $i = 5;
        $this->output->write(PHP_EOL . $this->getGreenText('The installation is almost complete'), false);
        while ($i > 0) {
            $this->output->write($this->getGreenText('.'), false);
            $i--;
            sleep(1);
        }
        $this->line(PHP_EOL . sprintf('%s
MineAdmin Version: %s
default username: superAdmin
default password: admin123', $this->getInfo(), Mine::getVersion()), 'comment');
    }

    /**
     * @param $extension
     */
    protected function checkExtension($extension): void
    {
        if (!extension_loaded($extension)) {
            $this->line(sprintf(" %s extension not install >>> %sNO!%s", $extension, self::CONSOLE_RED_BEGIN, self::CONSOLE_END));
            exit;
        }
        $this->line(sprintf(' %s extension is installed >>> %sOK!%s', $extension, self::CONSOLE_GREEN_BEGIN, self::CONSOLE_END));
    }
}
