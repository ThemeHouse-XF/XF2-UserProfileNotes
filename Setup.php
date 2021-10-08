<?php

namespace ThemeHouse\UserNotes;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->schemaManager()->createTable('xf_th_usernotes_usernote', function (Create $table) {
            $table->addColumn('user_note_id', 'int')->nullable()->autoIncrement();
            $table->addColumn('message', 'text');
            $table->addColumn('post_date', 'int')->setDefault(0);
            $table->addColumn('last_edit_date', 'int')->nullable();
            $table->addColumn('profile_user_id', 'int');
            $table->addColumn('user_id', 'int');
            $table->addColumn('username', 'varchar', 50);
        });
    }

    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('xf_th_usernotes_usernote');
    }
}