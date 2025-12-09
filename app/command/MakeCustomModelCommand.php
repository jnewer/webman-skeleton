<?php

namespace app\command;

use support\Db;
use Webman\Console\Util;
use Doctrine\Inflector\InflectorFactory;
use Webman\Console\Commands\MakeModelCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('make:custom-model', 'Make custom model')]
class MakeCustomModelCommand extends MakeModelCommand
{

    /**
     * @param $class
     * @param $namespace
     * @param $file
     * @param string|null $connection
     * @return void
     */
    protected function createModel($class, $namespace, $file, $connection = null)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $table = Util::classToName($class);
        $table_val = 'null';
        $pk = 'id';
        $properties = '';
        $connection = $connection ?: config('database.default');
        $timestamps = 'false';
        $hasCreatedAt = false;
        $hasUpdatedAt = false;
        try {
            $prefix = config("database.connections.$connection.prefix") ?? '';
            $database = config("database.connections.$connection.database");
            $driver = config("database.connections.$connection.driver") ?? 'mysql';
            $inflector = InflectorFactory::create()->build();
            $table_plura = $inflector->pluralize($inflector->tableize($class));
            $con = Db::connection($connection);

            // 检查表是否存在（兼容MySQL和PostgreSQL）
            if ($driver === 'pgsql') {
                // PostgreSQL 表检查
                $schema = config("database.connections.$connection.schema") ?? 'public';
                $exists_plura = $con->select("SELECT to_regclass('{$schema}.{$prefix}{$table_plura}') as table_exists");
                $exists = $con->select("SELECT to_regclass('{$schema}.{$prefix}{$table}') as table_exists");

                if (!empty($exists_plura[0]->table_exists)) {
                    $table_val = "'$table_plura'";
                    $table = "{$prefix}{$table_plura}";
                } else if (!empty($exists[0]->table_exists)) {
                    $table_val = "'$table'";
                    $table = "{$prefix}{$table}";
                }
            } else {
                // MySQL 表检查
                if ($con->select("show tables like '{$prefix}{$table_plura}'")) {
                    $table_val = "'$table_plura'";
                    $table = "{$prefix}{$table_plura}";
                } else if ($con->select("show tables like '{$prefix}{$table}'")) {
                    $table_val = "'$table'";
                    $table = "{$prefix}{$table}";
                }
            }

            // 获取表注释和列信息（兼容MySQL和PostgreSQL）
            if ($driver === 'pgsql') {
                // PostgreSQL 表注释
                $schema = config("database.connections.$connection.schema") ?? 'public';
                $tableComment = $con->select("SELECT obj_description('{$schema}.{$table}'::regclass) as table_comment");
                if (!empty($tableComment) && !empty($tableComment[0]->table_comment)) {
                    $comments = $tableComment[0]->table_comment;
                    $properties .= " * {$table} {$comments}" . PHP_EOL;
                }

                // PostgreSQL 列信息
                $columns = $con->select("
                    SELECT
                        a.attname as column_name,
                        format_type(a.atttypid, a.atttypmod) as data_type,
                        CASE WHEN con.contype = 'p' THEN 'PRI' ELSE '' END as column_key,
                        d.description as column_comment
                    FROM pg_catalog.pg_attribute a
                    LEFT JOIN pg_catalog.pg_description d ON d.objoid = a.attrelid AND d.objsubid = a.attnum
                    LEFT JOIN pg_catalog.pg_constraint con ON con.conrelid = a.attrelid AND a.attnum = ANY(con.conkey) AND con.contype = 'p'
                    WHERE a.attrelid = '{$schema}.{$table}'::regclass
                    AND a.attnum > 0 AND NOT a.attisdropped
                    ORDER BY a.attnum
                ");

                foreach ($columns as $item) {
                    if ($item->column_key === 'PRI') {
                        $pk = $item->column_name;
                        $item->column_comment = ($item->column_comment ? $item->column_comment . ' ' : '') . "(主键)";
                    }
                    $type = $this->getType($item->data_type);
                    if ($item->column_name === 'created_at') {
                        $hasCreatedAt = true;
                    }
                    if ($item->column_name === 'updated_at') {
                        $hasUpdatedAt = true;
                    }
                    // 使用 data_type，它已经包含了完整的类型信息
                    $properties .= " * @property $type \${$item->column_name} [{$item->data_type}] " . ($item->column_comment ?? '') . "\n";
                }
            } else {
                // MySQL 表注释
                $tableComment = $con->select('SELECT table_comment FROM information_schema.`TABLES` WHERE table_schema = ? AND table_name = ?', [$database, $table]);
                if (!empty($tableComment)) {
                    $comments = $tableComment[0]->table_comment ?? $tableComment[0]->TABLE_COMMENT;
                    $properties .= " * {$table} {$comments}" . PHP_EOL;
                }

                // MySQL 列信息 - 修改查询以获取 COLUMN_TYPE
                foreach ($con->select("select COLUMN_NAME,DATA_TYPE,COLUMN_TYPE,COLUMN_KEY,COLUMN_COMMENT from INFORMATION_SCHEMA.COLUMNS where table_name = '$table' and table_schema = '$database' ORDER BY ordinal_position") as $item) {
                    if ($item->COLUMN_KEY === 'PRI') {
                        $pk = $item->COLUMN_NAME;
                        $item->COLUMN_COMMENT .= "(主键)";
                    }
                    $type = $this->getType($item->DATA_TYPE);
                    if ($item->COLUMN_NAME === 'created_at') {
                        $hasCreatedAt = true;
                    }
                    if ($item->COLUMN_NAME === 'updated_at') {
                        $hasUpdatedAt = true;
                    }
                    // 使用 COLUMN_TYPE 替代 DATA_TYPE，因为它包含了完整的类型信息如 varchar(32)
                    $properties .= " * @property $type \${$item->COLUMN_NAME} [{$item->COLUMN_TYPE}] {$item->COLUMN_COMMENT}\n";
                }
            }
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        $properties = rtrim($properties) ?: ' *';
        $timestamps = $hasCreatedAt && $hasUpdatedAt ? 'true' : 'false';
        $model_content = <<<EOF
<?php

namespace $namespace;

use support\Model;

/**
$properties
 */
class $class extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected \$connection = '$connection';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = $table_val;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected \$primaryKey = '$pk';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public \$timestamps = $timestamps;


}

EOF;
        file_put_contents($file, $model_content);
    }
}
