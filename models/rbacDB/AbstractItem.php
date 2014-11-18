<?php
namespace webvimark\modules\UserManagement\models\rbacDB;

use webvimark\modules\UserManagement\UserManagementModule;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use Yii;
use yii\rbac\DbManager;


/**
 * @property integer $type
 * @property string $name
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $is_system
 */
abstract class AbstractItem extends ActiveRecord
{
	const TYPE_ROLE = 1;
	const TYPE_PERMISSION = 2;
	const TYPE_ROUTE = 3;

	/**
	 * Reassigned in child classes to type role, permission or route
	 */
	const ITEM_TYPE = 0;


	/**
	 * Useful helper for migrations and other stuff
	 *
	 * @param string      $name
	 * @param null|string $description
	 * @param null|string $ruleName
	 * @param null|string $data
	 * @param int         $is_system
	 *
	 * @return static
	 */
	public static function create($name, $description = null, $ruleName = null, $data = null, $is_system = 0)
	{
		$item = new static;

		$item->type = static::ITEM_TYPE;
		$item->name = $name;
		$item->description = ( $description === null AND static::ITEM_TYPE != static::TYPE_ROUTE ) ? $name : $description;
		$item->rule_name = $ruleName;
		$item->data = $data;
		$item->is_system = $is_system;

		$item->save();

		return $item;
	}

	/**
	 * @param mixed $condition
	 *
	 * @return bool
	 */
	public static function deleteIfExists($condition)
	{
		$model = static::findOne($condition);

		if ( $model )
		{
			$model->delete();
			return true;
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'auth_item';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'rule_name'], 'filter', 'filter' => 'trim'],

			['name', 'required'],
			['name', 'unique'],
			[['name', 'rule_name'], 'string', 'max' => 64],

			['rule_name', 'default', 'value'=>null],

			[['description', 'data'], 'safe'],

			['type', 'integer'],
			['type', 'in', 'range'=>[static::TYPE_ROLE, static::TYPE_PERMISSION, static::TYPE_ROUTE]],
		];
	}

	/**
	 * @inheritdoc
	 * @return ActiveQuery the newly created [[ActiveQuery]] instance.
	 */
	public static function find()
	{
		return parent::find()->andWhere(['auth_item.type'=>static::ITEM_TYPE]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'name'        => UserManagementModule::t('back', 'Name'),
			'description' => UserManagementModule::t('back', 'Description'),
			'rule_name'   => UserManagementModule::t('back', 'Rule'),
			'data'        => UserManagementModule::t('back', 'Data'),
			'type'        => UserManagementModule::t('back', 'Type'),
			'created_at'  => UserManagementModule::t('back', 'Created'),
			'updated_at'  => UserManagementModule::t('back', 'Updated'),
		];
	}

	/**
	 * Ensure type of item
	 *
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		$this->type = static::ITEM_TYPE;

		return parent::beforeSave($insert);
	}
} 