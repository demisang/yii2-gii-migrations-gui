<?php

namespace demi\gii\migration;


class Generator extends \yii\gii\Generator
{
    public $db = 'db';
    public $tableName;
    public $modelClass;
    public $customParam;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Migration Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an Migration class for the specified table name and fields.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [

        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [

        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [

        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        // @todo make 'query.php' to be required before 2.1 release
        return ['model.php'/*, 'query.php'*/];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['ns', 'db', 'baseClass', 'generateRelations', 'generateLabelsFromComments', 'queryNs', 'queryBaseClass']);
    }

    /**
     * Returns the `tablePrefix` property of the DB connection as specified
     *
     * @return string
     * @since 2.0.5
     * @see getDbConnection
     */
    public function getTablePrefix()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return $db->tablePrefix;
        } else {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            // model :
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = ($this->generateQuery) ? $this->generateQueryClassName($modelClassName) : false;
            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $modelClassName,
                'queryClassName' => $queryClassName,
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [],
            ];
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClassName . '.php',
                $this->render('model.php', $params)
            );

            // query :
            if ($queryClassName) {
                $params['className'] = $queryClassName;
                $params['modelClassName'] = $modelClassName;
                $files[] = new CodeFile(
                    Yii::getAlias('@' . str_replace('\\', '/', $this->queryNs)) . '/' . $queryClassName . '.php',
                    $this->render('query.php', $params)
                );
            }
        }

        return $files;
    }
}