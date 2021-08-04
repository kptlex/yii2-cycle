<?php

declare(strict_types=1);

namespace Lex\Yii\Cycle\Tests\Migration;

use Spiral\Migrations\Migration;

class V20201226180357_create_test_table extends Migration
{
    public function up(): void
    {
        $table = $this->table('test');
        $table->addColumn('id', 'primary');
        $table->addColumn('name', 'string');
        $table->create();
    }

    public function down(): void
    {
        $this->table('test')->drop();
    }
}
