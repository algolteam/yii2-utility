<?php
/********************************************************************************
 *                     .::ALGOL TEAMWORK PRODUCTIONS::.                          *
 *        .::Author © 2021 | algolitc@gmail.com | github.com/algolteam::.        *
 *********************************************************************************
 *  Description: This is class for PHP.                                          *
 *  Thanks to specialist: All PHP masters.                                       *
 ********************************************************************************/

use yii\db\Query;
use yii\db\ActiveRecord;
use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\AssetBundle;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/**
 * ALGOL_YII
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class ALGOL_YII {

    /**
     * @return AppOf
     */
    public static function AppOf() {
        return new AppOf();
    }

    /**
     * @return ActiveRecordOf
     */
    public static function ActiveRecordOf() {
        return new ActiveRecordOf();
    }

    /**
     * @return HtmlOf
     */
    public static function HtmlOf() {
        return new HtmlOf();
    }

    /**
     ** onclick function(model, key, index) {value, url, data, expand}
     ** loading string
     * @return string
     */
    public static function GridColumnExpandOf() {
        return GridColumnExpandOf::class;
    }

    /**
     * @return ModalOf
     */
    public static function ModalOf() {
        return new ModalOf();
    }
}

/**
 * AppOf
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class AppOf {

    /**
     * @return mixed|string|null
     */
    public function GetIP() {
        $FResult = Yii::$app->getRequest()->getUserIP();
        if (!isset($FResult)) {
            $FResult = (new SystemOf)->Values();
            if (isset($FResult['Network']['IPv4 Address'])) $FResult = $FResult['Network']['IPv4 Address']; else $FResult = '127.0.0.1';
        }
        return $FResult;
    }

    public function GetUserID() {

    }

    public function GetPost(&$AResult, $AName = null, $ADefaultValue = null) {
        $FResult = Yii::$app->request;
        $AResult = $FResult->post($AName, $ADefaultValue);
        return $FResult->isPost or (bool)$AResult;
    }

    public function ActiveActionCheck($AName, $ATrue, $AFalse) {
        return (new StrOf)->Same(Yii::$app->controller->action->id, $AName) ? $ATrue : $AFalse;
    }

    public function ActiveControllerCheck($AName, $ATrue, $AFalse) {
        return (new StrOf)->Same(Yii::$app->controller->id, $AName) ? $ATrue : $AFalse;
    }

    public function ActiveModuleCheck($AName, $ATrue, $AFalse) {
        return (new StrOf)->Same(Yii::$app->controller->module->id, $AName) ? $ATrue : $AFalse;
    }

}

/**
 * ActiveRecordOf
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class ActiveRecordOf extends ActiveRecord {

    const C_MYSQL_OPERATORS = [
        'BETWEEN', 'EXISTS', 'LIKE', 'IN', 'ALL', 'ANY', 'ISNULL', 'UNIQUE',
        '>', '<', '=', '!=', '>=', '<=', '!>', '!<', '<>',
    ];

    protected static $FTableName;

    /**
     * @param $ATableName
     * @return static
     */
    public static function useTable($ATableName) {
        static::$FTableName = $ATableName;
        return new static();
    }

    /**
     * @return string
     */
    public static function tableName() {
        return static::$FTableName;
    }

    private function JoinOf($AValues, Query &$AResult) {
        if ((new ArrayOf)->Length($AValues) > 0) {
            foreach ($AValues as $FKey => $FValue) $AResult->leftJoin($FKey, $FValue);
        } else {
            if ((new ArrayOf)->FromString($AValues, CH_SPEC, $FResult) == 2) $AResult->leftJoin((new ArrayOf)->Value($FResult), (new ArrayOf)->Value($FResult, 2));
        }
    }

    private function WhereOf($AKey, $AValue) {
        $FResult = null;
        $FNot = null;
        $FFieldName = null;
        $FOperant = null;
        $AKey = trim($AKey);
        if ((new ArrayOf)->FromString($AKey, CH_SPACE, $FSubResult) > 0) {
            foreach ($FSubResult as $FValue) {
                $FValueUp = trim((new StrOf)->CharCase($FValue, MB_CASE_UPPER));
                if (in_array($FValueUp, [CH_AND_TEXT, CH_OR_TEXT])) $FResult = [$FValueUp];
                elseif (in_array($FValueUp, $this->C_MYSQL_OPERATORS)) $FOperant = $FValueUp;
                elseif ($FValueUp == CH_NOT_TEXT) $FNot = CH_NOT_TEXT; else $FFieldName = $FValue;
            }
        }
        if (isset($FFieldName)) {
            if (isset($FOperant)) {
                $FOperantFull = (new DefaultOf)->ValueCheck($FNot, $FOperant, $FNot . CH_SPACE . $FOperant);
                switch ($FOperant) {
                    case 'BETWEEN':
                        $FResult = (new ArrayOf)->Of(AO_Merge, [$FOperantFull, $FFieldName], $AValue);
                        break;
                    case 'LIKE':
                        $FResult = [$FOperantFull, $FFieldName, $AValue];
                        break;
                    case 'IN':
                        $FFieldNameArr = (new ArrayOf)->FromStringWithArray($FFieldName);
                        if (((new ArrayOf)->Length($AValue) > 1) and ((new ArrayOf)->Length($AValue) == (new ArrayOf)->Length($FFieldNameArr))) $FResult = [$FOperantFull, $FFieldNameArr, [$AValue]]; else $FResult = [$FOperantFull, $FFieldName, $AValue];
                        break;
                    default:
                        $FResult = [$FOperantFull, $FFieldName, (new ArrayOf)->First($AValue)];
                        break;
                }
            } else $FResult = [$FFieldName => $AValue];
        } elseif (isset($FResult) and is_array($AValue)) {
            foreach ($AValue as $FKey => $FValue) $FResult[] = $this->WhereOf($FKey, $FValue);
        }
        if ($FNot and is_null($FOperant)) $FResult = [$FNot, $FResult];
        return $FResult;
    }

    public function Builder($ATableNames, $AColumns = null, $AJoins = null, $AWheres = null, $AGroups = null, $AHavings = null, $AOrders = null, $ALimit = null, $ACond = CH_AND_TEXT, Query $AQuery = null) {
        if (isset($AQuery)) $FResult = $AQuery; else $FResult = $this->useTable($ATableNames)::find();
        if ($FResult instanceof Query) $FResult->from($ATableNames);
        if (isset($AColumns)) $FResult->select($AColumns);
        if (isset($AJoins)) $this->JoinOf($AJoins, $FResult);
        if (isset($AWheres)) {
            if (is_string($AWheres)) $FResult->where($AWheres);
            elseif (is_int($AWheres)) $FResult->where(['ID' => $AWheres]); else $FResult->where($this->WhereOf($ACond, $AWheres));
        }
        if (isset($AGroups)) $FResult->groupBy($AGroups);
        if (isset($AHavings)) $FResult->having($AHavings);
        if (isset($AOrders)) $FResult->orderBy($AOrders);
        if (isset($ALimit)) $FResult->limit($ALimit);
        return $FResult;
    }

    public function BuilderQuery($ATableNames, $AColumns = null, $AJoins = null, $AWheres = null, $AGroups = null, $AHavings = null, $AOrders = null, $ALimit = null, $ACond = CH_AND_TEXT) {
        return $this->Builder($ATableNames, $AColumns, $AJoins, $AWheres, $AGroups, $AHavings, $AOrders, $ALimit, $ACond, new Query());
    }

    public function Filter($ATableName, $AValues, &$AResult, $AColumns = "*", $ANumRows = null, $AFormat = null, $AFormatClearSubArray = true, $AOrder = null, $AGroup = null, $AJoin = null, $AJSONParseField = null, $ACond = CH_AND_TEXT) {
        $AResult = null;
        $FResult = $this->Builder($ATableName, $AColumns, $AJoin, $AValues, $AGroup, null, $AOrder, $ANumRows, $ACond);
        if ($FResult) {
            if ($ANumRows === 1) $AResult = $FResult->asArray()->one(); else $AResult = $FResult->asArray()->all();
            if ($AResult) {
                // Get JSON parsed
                if ((new StrOf)->Length($AJSONParseField) > 0) $AResult = (new ArrayOf)->FromJSON($AResult, $AJSONParseField);
                // Get format
                if (($ANumRows <> 1) and !is_null($AFormat)) {
                    (new ArrayOf)->FromFormat($AResult, $AFormat, $AResult, CH_FREE, CH_FREE, $AFormatClearSubArray);
                }
            }
        }
        return (new ArrayOf)->Length($AResult) > 0;
    }

    public function FilterOne($ATableName, $AValues, &$AResult, $AColumns = "*", $AOrder = null, $AGroup = null, $AJoin = null, $AJSONParseField = null, $ACond = CH_AND_TEXT) {
        return $this->Filter($ATableName, $AValues, $AResult, $AColumns, 1, null, true, $AOrder, $AGroup, $AJoin, $AJSONParseField, $ACond);
    }

    public function Append($ATableName, $AValues, &$AResult, $AMultiInsert = false) {
        $FResult = null;
        $AResult = null;
        if ($AMultiInsert) {
            $FResult = ((new ArrayOf)->Length($AValues) == 2) and Yii::$app->db->createCommand()->batchInsert($ATableName, $AValues[0], $AValues[1])->execute();
        } else {
            $AResult = $this->useTable($ATableName);
            if (isset($AResult)) {
                foreach ($AValues as $FKey => $FValue) {
                    $AResult->setAttribute($FKey, $FValue);
                }
                $FResult = $AResult->save();
            } else {
                $FResult = ((new ArrayOf)->Length($AValues) == 2) and Yii::$app->db->createCommand()->insert($ATableName, $AValues)->execute();
            }
            if ($FResult) $AResult = Yii::$app->db->getLastInsertID();
        }
        return $FResult;
    }

    public function Edit($ATableName, $AValues, $AFilter = CH_FREE, $AParam = []) {
        if ((new DefaultOf)->TypeCheck($AFilter)) {
            $FFilter = "ID = $AFilter";
            $FParam = null;
        } else {
            $FFilter = $AFilter;
            $FParam = $AParam;
            $this->BindOf($FFilter, $FParam);
        }
        return Yii::$app->db->createCommand()->update($ATableName, $AValues, $FFilter, $FParam)->execute();
    }

    public function Deleted($ATableName, $AFilter = CH_FREE, $AParam = []) {
        if ((new DefaultOf)->TypeCheck($AFilter)) {
            $FFilter = "ID = $AFilter";
            $FParam = null;
        } else {
            $FFilter = $AFilter;
            $FParam = $AParam;
            $this->BindOf($FFilter, $FParam);
        }
        return Yii::$app->db->createCommand()->delete($ATableName, $FFilter, $FParam)->execute();
    }

    private function BindOf(&$AQuery, &$AParam) {
        $FResult = null;
        if (!(new StrOf)->Empty($AQuery) and !(new ArrayOf)->Empty($AParam)) {
            $FMoneyFound = false;
            foreach ($AParam as $FKey => $FValue) {
                if ((new StrOf)->Pos($AQuery, CH_MONEY . $FKey) > 0) {
                    $FResult[CH_POINT_TWO_VER . $FKey] = $FValue;
                    $FMoneyFound = true;
                } elseif ((new StrOf)->FoundWord($AQuery, $FKey)) {
                    $FResult[CH_POINT_TWO_VER . $FKey] = $FValue;
                    $AQuery = (new StrOf)->Replace($AQuery, $FKey, CH_POINT_TWO_VER . $FKey);
                }
            }
            if (isset($FResult)) {
                $AParam = $FResult;
                if ($FMoneyFound) $AQuery = (new StrOf)->Replace($AQuery, CH_MONEY, CH_POINT_TWO_VER);
            }
        }
        return isset($FResult);
    }

    private function Execute($ASqlQuery, &$AResult, $AValues = null) {
        $AResult = null;
        if (!(new StrOf)->Empty($ASqlQuery)) {
            $FValues = $AValues;
            $FQuery = $ASqlQuery;
            if ($this->BindOf($FQuery, $FValues)) $AResult = Yii::$app->db->createCommand($FQuery, $FValues); else $AResult = Yii::$app->db->createCommand($FQuery);
        }
        return (bool)$AResult;
    }

    public function SqlToAll($ASqlQuery, &$AResult, $AValues = null) {
        if ($this->Execute($ASqlQuery, $AResult, $AValues)) {
            $AResult = $AResult->queryAll();
        }
        return (bool)$AResult;
    }

    public function SqlToOne($ASqlQuery, &$AResult, $AValues = null) {
        if ($this->Execute($ASqlQuery, $AResult, $AValues)) {
            $AResult = $AResult->queryOne();
        }
        return (bool)$AResult;
    }

    public function SqlToColumn($ASqlQuery, &$AResult, $AValues = null) {
        if ($this->Execute($ASqlQuery, $AResult, $AValues)) {
            $AResult = $AResult->queryColumn();
        }
        return (bool)$AResult;
    }

    public function SqlToScalar($ASqlQuery, &$AResult, $AValues = null) {
        if ($this->Execute($ASqlQuery, $AResult, $AValues)) {
            $AResult = $AResult->queryScalar();
        }
        return (bool)$AResult;
    }

    public function SqlToExecute($ASqlQuery, &$AResult, $AValues = null) {
        if ($this->Execute($ASqlQuery, $AResult, $AValues)) {
            $AResult = $AResult->execute();
        }
        return (bool)$AResult;
    }

    public function SqlToActiveQuery($ASqlQuery, &$AResult, $AValues = null) {
        $AResult = null;
        if (!(new StrOf)->Empty($ASqlQuery)) {
            $FValues = $AValues;
            $FQuery = $ASqlQuery;
            if ($this->BindOf($FQuery, $FValues)) $AResult = self::findBySql($FQuery, $FValues); else $AResult = self::findBySql($FQuery);
        }
        return (bool)$AResult;
    }

    public function Counter($ATableName, $AColumn, $AFilters, $AValue = 1) {
        if ($AValue > 0) {
            if ($this->SqlToExecute("UPDATE $ATableName SET $AColumn = $AColumn + $AValue WHERE $AFilters", $FResult)) return $FResult; else return false;
        } elseif ($AValue < 0) {
            $FValue = -1 * $AValue;
            if ($this->SqlToExecute("UPDATE $ATableName SET $AColumn = $AColumn - $FValue WHERE $AFilters", $FResult)) return $FResult; else return false;
        } else return false;
    }

}

// Const ToGo
const TGT_Link = "TGT_Link";
const TGT_Mail = "TGT_Mail";
const TGT_Submit = "TGT_Submit";
const TGT_Button = "TGT_Button";
const TGT_Reset = "TGT_Reset";
const TGT_DataPost = "TGT_DataPost";
const TGT_FormPost = "TGT_FormPost";

// Const Get Array Of
const AA_SqlAll = "AA_SqlAll";
const AA_SqlOne = "AA_SqlOne";
const AA_SqlCount = "AA_SqlCount";
const AA_Url = "AA_Url";

/**
 * HtmlOf
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class HtmlOf {

    public function GetFormToken() {
        return Html::hiddenInput('_csrf', Yii::$app->request->getCsrfToken());
    }

    public function ToGo($AValue, $AUrl, $AData = null, $AType = TGT_Link, $AOptions = null) {
        $FResult = CH_FREE;
        if (!(new StrOf)->Empty([$AValue, $AUrl])) {
            // Get Param
            $FValue = $AValue;
            $FUrl = [$AUrl];
            $FOptions = $AOptions;
            // Check Post
            if ($AType === TGT_FormPost) {
                $FResult = Html::beginForm($FUrl, 'post', ['class' => 'form-inline']);
                if (!is_null($AData)) {
                    foreach ($AData as $FKey => $FItem) {
                        $FResult .= html::hiddenInput($FKey, $FItem);
                    }
                }
                $FResult .= Html::submitButton($FValue, $FOptions) . Html::endForm();
            } else {
                // Check post
                if ($AType === TGT_DataPost) {
                    $FOptions['data-method'] = 'POST';
                    if (!is_null($AData)) $FOptions['data-params'] = $AData;
                } else {
                    $FUrl = (new ArrayOf)->Of(AO_Merge, $FUrl, $AData);
                }
                // Check url
                switch ($AType) {
                    case 'home':
                        $FUrl = Url::home();
                        break;
                    case 'current':
                        $FUrl = Url::current();
                        break;
                }
                // Check type
                switch ($AType) {
                    case TGT_Mail:
                        $FResult = Html::mailto($FValue, $AUrl, $FOptions);
                        break;
                    case TGT_Button:
                        $FResult = Html::button($FValue, $FOptions);
                        break;
                    case TGT_Submit:
                        $FResult = Html::submitButton($FValue, $FOptions);
                        break;
                    case TGT_Reset:
                        $FResult = Html::resetButton($FValue, $FOptions);
                        break;
                    default:
                        $FResult = Html::a($FValue, $FUrl, $FOptions);
                        break;
                }
            }
        }
        return $FResult;
    }

    public function FromAction($AValues, $AParam) {
        if ((new StrOf)->Found($AParam, [AA_SqlAll, AA_SqlOne, AA_SqlCount, AA_Url])) {
            $FResult = $AValues;
            if (!(new ArrayOf)->Empty($FResult) and !(new ArrayOf)->Empty($AParam) and isset($AParam['action']) and isset($AParam['name'])) {
                $FAction = $AParam['action'];
                $FName = $AParam['name'];
                switch ($FAction) {
                    case AA_SqlAll:
                        $FDefault = (new DefaultOf)->ValueCheck($AParam['default'], CH_FREE);
                        $FQuery = (new StrOf)->Replace((new DefaultOf)->ValueCheck($AParam['query'], CH_FREE), array_keys($FResult), array_values($FResult));
                        $FData = (new ArrayOf)->Of(AO_Merge, $FResult, $AParam['data']);
                        if ((new ActiveRecordOf)->SqlToAll($FQuery, $FSubResult, $FData)) {
                            if (isset($AParam['format'])) {
                                $FResult[$FName] = $this->FromArray($FSubResult, $AParam['format'], $FDefault, $AParam['interval']);
                            } elseif (!(new ArrayOf)->Empty($FSubResult)) {
                                $FResult[$FName] = (new ArrayOf)->ToString($FSubResult, (new DefaultOf)->ValueCheck($AParam['interval'], CH_COMMA . CH_SPACE));
                            } else $FResult[$FName] = $FDefault;
                        } else $FResult[$FName] = $FDefault;
                        break;
                    case AA_SqlOne:
                        $FDefault = (new DefaultOf)->ValueCheck($AParam['default'], CH_FREE);
                        $FQuery = (new StrOf)->Replace((new DefaultOf)->ValueCheck($AParam['query'], CH_FREE), array_keys($FResult), array_values($FResult));
                        $FData = (new ArrayOf)->Of(AO_Merge, $FResult, $AParam['data']);
                        if ((new ActiveRecordOf)->SqlToOne($FQuery, $FSubResult, $FData)) {
                            if (isset($AParam['format'])) {
                                $FResult[$FName] = (new StrOf)->Replace($AParam['format'], array_keys($FSubResult), array_values($FSubResult));
                            } elseif (!(new ArrayOf)->Empty($FSubResult)) {
                                $FResult[$FName] = (new ArrayOf)->ToString($FSubResult, (new DefaultOf)->ValueCheck($AParam['interval'], CH_COMMA . CH_SPACE));
                            } else $FResult[$FName] = $FDefault;
                        } else $FResult[$FName] = $FDefault;
                        break;
                    case AA_SqlCount:
                        $FDefault = (new DefaultOf)->ValueCheck($AParam['default'], CH_FREE);
                        $FQuery = (new StrOf)->Replace((new DefaultOf)->ValueCheck($AParam['query'], CH_FREE), array_keys($FResult), array_values($FResult));
                        $FData = (new ArrayOf)->Of(AO_Merge, $FResult, $AParam['data']);
                        if ((new ActiveRecordOf)->SqlToExecute($FQuery, $FSubResult, $FData)) {
                            if (isset($AParam['format'])) {
                                $FResult[$FName] = (new StrOf)->Replace($AParam['format'], 'count', $FSubResult);
                            } else $FResult[$FName] = $FDefault;
                        } else $FResult[$FName] = $FDefault;
                        break;
                    case AA_Url:
                        $FResult[$FName] = (new StrOf)->Replace(Url::to((new DefaultOf)->ValueCheck($AParam['url'], 'home')), array_keys($FResult), array_values($FResult));
                        break;
                }
            }
            return $FResult;
        } else return (new ArrayOf)->FromAction($AValues, $AParam);
    }

    public function FromArray($AValues, $AFormat, $ADefault = CH_FREE, $AInterval = CH_FREE, $AParam = null) {
        $FValues = $AValues;
        if (!(new ArrayOf)->Empty($FValues) and !(new ArrayOf)->Empty($AParam)) {
            foreach ($FValues as $FKey => $FValue) {
                if (is_array($FValue)) {
                    foreach ($AParam as $FParamValue) {
                        $FValues[$FKey] = $this->FromAction($FValues[$FKey], $FParamValue);
                    }
                }
            }
        }
        return (new ArrayOf)->FromFormat($FValues, $AFormat, $FResult, $ADefault, $AInterval);
    }

    public function CreateJsFunction($AElementID, $AEvent, $ACode, $AParam = null) {
        $FArguments = null;
        $FVariables = null;
        $FCode = $ACode;
        if (isset($AElementID)) {
            if (($AElementID[0] == CH_POINT) or (new StrOf)->Found($AElementID, [CH_NET, CH_BRACE_SQR_BEGIN, CH_POINT_TWO_VER])) $FElementID = "'$AElementID'";
            elseif (in_array($AElementID, ['window'])) $FElementID = $AElementID;
            else $FElementID = "'#$AElementID'";
        }
        if (!(new ArrayOf)->Empty($AParam)) {
            foreach ($AParam as $FVarName => $FElemID) {
                if ((new StrOf)->Same($FElemID, 'arg')) {
                    $FArguments = (new StrOf)->Add($FArguments, $FVarName, CH_COMMA, true);
                } else {
                    $FVariables = (new StrOf)->Add($FVariables, "   var $FVarName = document.getElementById('#$FElemID');", CH_NEW_LINE, true);
                }
            }
            $FArguments = trim($FArguments);
            $FVariables = trim($FVariables);
        }
        if (!(new ArrayOf)->Empty($ACode)) $FCode = (new ArrayOf)->ToString($FCode, CH_POINT_COMMA . CH_NEW_LINE, true, '   %s');
        $FCode = (new StrOf)->Replace($FCode . CH_POINT_COMMA, CH_POINT_COMMA . CH_POINT_COMMA, CH_POINT_COMMA);
        if (!(new StrOf)->Empty($FVariables)) $FCode = $FVariables . CH_NEW_LINE . $FCode;
        if (isset($AElementID)) {
            $js = <<<JS
$($FElementID).on('$AEvent', function($FArguments) {
$FCode
})

JS;
        } else {
            $js = <<<JS
function $AEvent($FArguments) {
$FCode
}

JS;
        }

        return Yii::$app->getView()->registerJs($js, \yii\web\View::POS_END);
    }

}

/**
 * GridColumnExpandOf
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class GridColumnExpandOf extends DataColumn {
    const EXPANDED_CLASS = 'expand-column';
    const REDIRECT = 'redirect';

    public $enableCache = true;
    public $loading = '<center><h6><i>Please, wait...</i></h6></center>';
    public $onclick;

    private $url_expand;
    private $url_goto;
    private $column_id;

    /**
     *
     */
    public function init() {
        parent::init();
        if (isset($this->onclick)) {
            $this->url_expand = null;
            $this->url_goto = null;
            $this->column_id = md5(VarDumper::dumpAsString(get_object_vars($this), 5));
            ExpandColumnAsset::register($this->grid->getView());
        }
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    public function renderDataCell($model, $key, $index) {
        if ($this->onClick($FResult, $model, $key, $index)) {
            return Html::beginTag('td', $FResult['options'])
                . $FResult['content']
                . Html::endTag('td');
        }
        return parent::renderDataCell($model, $key, $index);
    }

    /**
     * @param $AResult
     * @param $model
     * @param $key
     * @param $index
     * @return bool
     */
    private function onClick(&$AResult, $model, $key, $index) {
        $AResult = null;
        $FOnClick = (new ArrayOf)->FromFunction($this->onclick, $model, $key, $index);
        if ((new ArrayOf)->Length($FOnClick) > 1) {
            if ((new StrOf)->Length($FOnClick['value'], true) > 0) {
                $AResult['content'] = $FOnClick['value'];
                if ((new DefaultOf)->TypeCheck($AResult['content'], DTC_HTML)) $this->format = 'raw';
            }
            if ((new StrOf)->Length($FOnClick['url'], true) > 0) {
                $FOptions = (new ArrayOf)->FromFunction($this->contentOptions, $model, $key, $index);
                if ((new DefaultOf)->ValueCheck($FOnClick['expand'], true)) {
                    if (is_null($this->url_expand)) {
                        $this->url_expand = $FOnClick['url'];
                        $this->regScript(true);
                    }
                    $FOptions['data-row_id'] = $this->normalizeRowID($key);
                    $FOptions['data-col_id'] = $this->column_id;
                    $FClass = $this->EXPANDED_CLASS . CH_MINUS . $this->column_id;
                } else {
                    if (is_null($this->url_goto)) {
                        $this->url_goto = $FOnClick['url'];
                        $this->regScript(false);
                    }
                    $FClass = $this->EXPANDED_CLASS . CH_MINUS . $this->REDIRECT;
                }
                $FOptions['class'] = $FClass . (isset($FOptions['class']) ? " {$FOptions['class']}" : CH_FREE);
                if ((new ArrayOf)->Length($FOnClick['data']) > 0) $FOptions['data-info'] = $FOnClick['data']; else $FOptions['data-info'] = is_array($key) ? $key : ['id' => $key];
                $AResult['options'] = $FOptions;
            }
        }
        return isset($AResult);
    }

    /**
     * @param bool $AExpand
     */
    private function regScript($AExpand = true) {
        if (Yii::$app->getRequest()->getIsAjax()) return;
        if ($AExpand) {
            $FClass = $FClass = $this->EXPANDED_CLASS . CH_MINUS . $this->column_id;
            $FOptions = Json::encode(['url' => $this->url_expand,
                'countColumns' => count($this->grid->columns),
                'enableCache' => (bool)$this->enableCache,
                'loading' => $this->loading,
                'hideEffect' => 'fadeOut',
                'showEffect' => 'fadeIn',
                'redirect' => false]);
        } else {
            $FClass = $FClass = $this->EXPANDED_CLASS . CH_MINUS . $this->REDIRECT;
            $FOptions = Json::encode(['url' => $this->url_goto, 'redirect' => true]);
        }

        $js = <<<JS
            jQuery(document).on('click', '#{$this->grid->getId()} .{$FClass}', function() {
                var row = new ExpandRow({$FOptions});
                row.run($(this));
            });
JS;
        return $this->grid->getView()->registerJs($js);
    }

    /**
     * @param $rowID
     * @return string
     */
    protected function normalizeRowID($rowID) {
        if (is_array($rowID)) {
            $rowID = implode('', $rowID);
        }
        return trim(preg_replace("|[^\d\w]+|iu", '', $rowID));
    }
}

/**
 * ExpandColumnAsset
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class ExpandColumnAsset extends AssetBundle {
    public $sourcePath = '@vendor/algolteam/library-yii2/assets';
    public $js = ['js/expand-column.js'];
    public $css = ['css/expand-column.css'];
    public $depends = ['yii\web\YiiAsset', 'yii\web\JqueryAsset'];
}

/**
 * SerializeJsonAsset
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class SerializeJsonAsset extends AssetBundle {
    public $sourcePath = '@vendor/algolteam/library-yii2/assets';
    public $js = ['js/serializeJSON.js'];
    public $depends = ['yii\web\JqueryAsset'];
}

/**
 * ModalOf
 *
 * @category  Class
 * @package   Utility-Yii2
 * @author    AlgolTeam <algolitc@gmail.com>
 * @copyright Copyright (c) 2021
 * @link      https://github.com/algolteam
 */

class ModalOf extends \yii\base\Widget {

    public $click;
    public $action;
    public $loader;
    public $title = 'Title';
    public $content;
    public $fields;
    public $submit = 'submit';
    public $method = 'post';
    public $empty = true;
    public $cssClass;

    public $modalOptions = [];
    public $formOptions = [];

    private $jsOptions;

    public function init() {
        parent::init();
        ob_start();
        SerializeJsonAsset::register($this->getView());
    }

    public function run() {
        parent::run();
        if (empty($this->action)) $this->action = Yii::$app->getUniqueId();
        return $this->renderAll($this->getId(), ob_get_clean());
    }

    private function renderAll($AID, $AContent) {
        $FResult = null;
        if (isset($this->click, $this->action)) {
            $this->OptionInit($AID);
            $FResult = $this->renderClick($AID) . $this->renderModal($AID, $AContent);
            if (!empty($FResult)) $this->renderJS($AID);
        }
        return $FResult;
    }

    private function renderClick($AID) {
        $FResult = $this->click;
        if (isset($FResult)) {
            // Click Create
            if (is_array($FResult)) {
                $FElementName = "$AID-click";
                $FTag = ArrayHelper::remove($FResult, 'tag', 'button');
                $FLabel = ArrayHelper::remove($FResult, 'label', 'Click me');
                $FResult = array_merge($FResult, ['id' => $FElementName]);
                $FResult = Html::tag($FTag, $FLabel, $FResult);
            } else {
                $FElementName = $FResult;
                $FResult = null;
            }
            // JS
            $this->jsOptions['click'] = $FElementName;
        }
        return $FResult;
    }

    private function renderModal($AID, $AContent) {
        // Header, Body, Footer
        $FResult = $this->renderHeader($AID);
        if (!empty($FResult)) {
            $FResult .= $this->renderBody($AID, $AContent);
            // Modal, Form
            $FModalOptions = $this->modalOptions;
            $FFormOptions = $this->formOptions;
            $FModalOptions = array_replace_recursive(['style' => [
                'background-color' => 'rgb(0,0,0)',
                'background-color' => 'rgba(0,0,0,0.4)']
            ], $FModalOptions, [
                'id' => "$AID-modal",
                'style' => [
                    'display' => 'none',
                    'position' => 'fixed',
                    'z-index' => 99999,
                    'padding-top' => '100px',
                    'left' => 0,
                    'top' => 0,
                    'width' => '100%',
                    'height' => '100%',
                    'box-sizing' => 'content-box',
                    'overflow' => 'auto']
            ]);
            $FFormOptions = array_replace_recursive([
                'style' => [
                    'background-color' => '#fefefe',
                    'border' => '1px solid #888',
                    'width' => '40%',
                    'box-shadow' => '0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19)']
            ], $FFormOptions, [
                'id' => "$AID-form",
                'style' => [
                    'position' => 'relative',
                    'margin' => 'auto',
                    'padding' => '0']
            ]);
            $FResult = Html::beginTag('div', $FModalOptions) .
                Html::beginTag('div', $FFormOptions) .
                $FResult .
                Html::endTag('div') .
                Html::endTag('div');
            // JS
            $this->jsOptions['modal']['window'] = true;
        }
        return $FResult;
    }

    private function renderHeader($AID) {
        $FResult = null;
        if (!is_null($this->title)) {
            $FTitle = $this->title;
            $FCloseTitle = '&times;';
            if (is_array($FTitle)) {
                $FOptions = $FTitle;
                $FTitle = ArrayHelper::remove($FOptions, 'label', 'Title');
                $FCloseOptions = ArrayHelper::remove($FOptions, 'close', []);
                $FCloseTitle = ArrayHelper::remove($FCloseOptions, 'label', $FCloseTitle);
            } else {
                $FOptions = [];
                $FCloseOptions = [];
            }
            // Header
            $FOptions = array_replace_recursive([
                'style' => [
                    'padding' => '2px 16px',
                    'background-color' => '#5cb85c',
                    'font-size' => 'x-large',
                    'cursor' => 'default',
                    'color' => 'white']
            ], $FOptions, [
                'id' => "$AID-header",
                'style' => [
                    'display' => 'flex',
                    'flex-flow' => 'row-reverse',
                    'align-items' => 'center',
                    'justify-content' => 'space-between']
            ]);
            $FCloseOptions = array_replace_recursive([
                'style' => [
                    'color' => 'white',
                    'float' => 'right',
                    'cursor' => 'pointer']
            ], $FCloseOptions, ['id' => "$AID-close"]);
            $FResult = Html::beginTag('div', $FOptions) .
                Html::beginTag('span', $FCloseOptions) .
                $FCloseTitle .
                Html::endTag('span') .
                $FTitle .
                Html::endTag('div');
            // JS
            $this->jsOptions['header']['close'] = true;
        }
        return $FResult;
    }

    private function renderBody($AID, $AContent) {
        // Body
        $FContent = $this->content;
        if (is_array($FContent)) {
            $FOptions = $FContent;
            $FContent = ArrayHelper::remove($FOptions, 'label', $AContent);
            $FAction = ArrayHelper::remove($FOptions, 'action', $this->action);
            $FMethod = ArrayHelper::remove($FOptions, 'method', $this->method);
        } else {
            $FOptions = [];
            $FContent = (new DefaultOf)->ValueCheck($AContent, $FContent);
            $FAction = $this->action;
            $FMethod = $this->method;
        }
        $FOptions = array_replace_recursive(['style' => [
            'padding' => '16px 16px']
        ], $FOptions, ['id' => "$AID-content"]);
        $FResult = Html::beginForm($FAction, $FMethod, $FOptions) .
            $FContent .
            Html::endForm();
        // JS
        $this->jsOptions['body']['content'] = (bool)empty($FContent);
        return $FResult;
    }

    private function renderJS($AID) {
        if(isset($this->jsOptions['click'])) {
            // submit code
            if ($this->empty) $FEmpty = "find(':input').filter(function () { return $.trim(this.value).length > 0 })."; else $FEmpty = CH_FREE;
            $FCodeSubmit = ["event.preventDefault()",
                "var form = $('#$AID-content, #$AID-content form, #$AID-content div')",
                "const formSerialize = form.". $FEmpty ."serializeJSON(), formData = {'_csrf': formSerialize._csrf, status: 'submit', this: formSerialize.this}",
                "delete formSerialize._csrf; delete formSerialize.this",
                "form.children('input[type=file]').each(function () { if (this.value.trim().length > 0) {formSerialize[this.name]=this.value;} })",
                "formData.data = formSerialize",
//                "$.each(this.attributes, function (index, attribute) { if (attribute.value.trim().length !== 0) {formData[attribute.name] = attribute.value;} })",
//                "$.post('$this->action', {status: 'submit', data: formData})",
                "$.ajax({
                    url: '$this->action', 
                    type: '$this->method', 
                    data: formData,
                    " . ((isset($this->loader)) ? "
                    beforeSend: function() {
                      $('$this->loader').show();
                    }, " : CH_FREE) . "                     
                    success: function (data) {
                        " . ((isset($this->loader)) ? "
                        $('$this->loader').hide();
                        " : CH_FREE) . "
                        if (!data || (data == 0)) {
                            $('#$AID-content').trigger('reset');
                            return false;
                        }
                        if (data == 1) {
                            document.location.reload(true);
                            $('#$AID-content').trigger('reset');
                            return false;
                        }
                        try {
                            const fdata = JSON.parse(data);
                        } catch (error) {
                            alert(error);
                            $('#$AID-content').trigger('reset');
                            return false;
                        }
                        if (fdata.id) {
                            var element = document.querySelector('[name=\''+fdata[item].id+'\']');
                            element = element ? element : document.querySelector(fdata[item].id);
                            if (element) {
                                if (!fdata.click) {
                                    var elem = $('[name=\''+fdata.id+'\'], '+fdata.id);                                   
                                    elem.unbind('click');                                
                                }
                                if (fdata.html) element.innerHTML = fdata.html;
                                if (fdata.value) element.setAttribute('value', fdata.value);
                            }
                        }
                        if (Object.keys(fdata).length > 0) {
                            for (const item in fdata) {
                                if (fdata[item].id) {
                                    var element = document.querySelector('[name=\''+fdata[item].id+'\']');
                                    element = element ? element : document.querySelector(fdata[item].id);
                                    if (element) {
                                        if (!fdata[item].click) {
                                            var elem = $('[name=\''+fdata[item].id+'\'], '+fdata[item].id);                                   
                                            elem.unbind('click');
                                        }
                                        if (fdata[item].html) element.innerHTML = fdata[item].html;
                                        if (fdata[item].value) element.setAttribute('value', fdata[item].value);
                                    }                                
                                }
                            }
                        }
                        if (fdata.message) {
                            alert(fdata.message);
                        }                        
                        if (fdata.url) {
                            location.href = fdata.url;
                        } else if (fdata.refresh) {
                            document.location.reload(true);                        
                        }
                        $('#$AID-content').trigger('reset');
                        return false; 
                    },
                    error: function(xhr, textStatus, error){
                        " . ((isset($this->loader)) ? "
                        $('$this->loader').hide();
                        " : CH_FREE) . "                     
                        alert(xhr.responseText);
                        $('#$AID-content').trigger('reset');
//                        debugger;
                    }
                })",
                "$('#$AID-close').click()"];
            $FCodeSubmitStr = ALGOL::ArrayOf()->ToString($FCodeSubmit, CH_POINT_COMMA);
            // show click
            $FCode = ["event.preventDefault();"]; //dataType: 'json',
            $FCode[] = "document.querySelectorAll('.$AID-remove').forEach(el => el.remove())";
            $FCode[] = "if (this.hasAttribute('id') == false) { this.setAttribute('id', Math.random().toString(36).substr(2, 9)); }";
            if ($this->jsOptions['body']['content']) $FCode[] = "
                var showData = {_csrf: document.getElementById('$AID-content').elements['_csrf'].value, status: 'content'};
                var addData = '<input type=\'hidden\' name=\'_csrf\' value=\''+showData._csrf+'\'>';
                addData += '<input type=\'hidden\' name=\'this[content]\' value=\''+this.textContent.trim()+'\' class=\'$AID-remove\' />';
                var this_attr = {content: this.textContent.trim()};
                $.each(this.attributes, function(index, attr) {
                    this_attr[attr.name] = attr.value;
                    addData += '<input type=\'hidden\' name=\'this['+attr.name+']\' value=\''+attr.value.trim()+'\' />'; 
                } );
                showData['this'] = this_attr;                                      
                $.ajax({                        
                    url: '$this->action', 
                    type: '$this->method', 
                    dataType: 'html', 
                    data: showData,
                    " . ((isset($this->loader)) ? "
                    beforeSend: function() {
                      $('$this->loader').show();
                    }, " : CH_FREE) . "                    
                    success: function (data) {
                        " . ((isset($this->loader)) ? "
                        $('$this->loader').hide();
                        " : CH_FREE) . "                    
                        $('#$AID-content').html(addData+data);
                        $('#$AID-modal #$this->submit,#$AID-modal :submit').on('click', function(event) {
                        $FCodeSubmitStr
                        })                        
                        return false;
                    },
                    error: function(xhr, textStatus, error){
                        " . ((isset($this->loader)) ? "
                        $('$this->loader').hide();
                        " : CH_FREE) . "                     
                        alert(xhr.responseText);
//                        debugger;
                    }                     
               })";
            $FCode[] = "document.getElementById('$AID-content').reset();";
            $FCode[] = "$('#$AID-modal').css({display: 'block'})";
            $FCode[] = "$('#$AID-content').append('<input type=\'hidden\' name=\'this[content]\' value=\''+this.textContent.trim()+'\' class=\'$AID-remove\' />')";
            $FCode[] = "$(this).each(function() { $.each(this.attributes, function() { if (this.specified) {
                        var input = document.createElement('input');
                        input.setAttribute('type', 'hidden');
                        input.setAttribute('name', 'this['+this.name+']');
                        input.setAttribute('value', this.value.trim());
                        input.setAttribute('class', '$AID-remove');
                        document.getElementById('$AID-content').appendChild(input);
                        } }); });";   // $('#$AID-content').append('<input type=hidden name='+this.name+' value='+this.value+' />');
            if (is_array($this->fields)) {
                $FFields = json_encode($this->fields);
                $FCode[] = "var fields = $FFields;
                            var elements = document.getElementById('$AID-content').elements;
                            $.each(fields, function(key, value) {
                                var el = elements[value.name];
                                if (el) el.setAttribute(value.event, 'fields(this)');
                           });";
                $FCode2 = ["var formData = {_csrf: document.getElementById('$AID-content').elements['_csrf'].value, status: 'fields', data: el.value}",
                    "var this_attr = {}",
                    "if (el.hasAttribute('id') == false) { el.setAttribute('id', Math.random().toString(36).substr(2, 9)); }",
                    "$.each(el.attributes, function(index, attr) {
                                this_attr[attr.name] = attr.value.trim();
                           } ); ",
                    "formData['this'] = this_attr",
                    "$.ajax({
                                url: '$this->action', 
                                type: '$this->method',
                                data: formData,
                                " . ((isset($this->loader)) ? "
                                beforeSend: function() {
                                  $('$this->loader').show();
                                }, " : CH_FREE) . "
                                success: function (data) {
                                    " . ((isset($this->loader)) ? "
                                    $('$this->loader').hide();
                                    " : CH_FREE) . "                                 
                                    if (!data) return false;
                                    const fdata = JSON.parse(data); 
                                    if (fdata.id) {
                                        var element = document.querySelector('[name=\''+fdata.id+'\'], '+fdata.id);
                                        if (element) {
                                            if (fdata.html) element.innerHTML = fdata.html;
                                            if (fdata.value) element.setAttribute('value', fdata.value);
                                        }
                                    } else {
                                        for (const item in fdata) {
                                            var element = document.querySelector('[name=\''+fdata[item].id+'\'], '+fdata[item].id);
                                            if (element) {
                                                if (fdata[item].html) element.innerHTML = fdata[item].html;
                                                if (fdata[item].value) element.setAttribute('value', fdata[item].value);
                                            }
                                        }
                                    }
                                    return false; 
                                },
                                error: function(xhr, textStatus, error){
                                    " . ((isset($this->loader)) ? "
                                    $('$this->loader').hide();
                                    " : CH_FREE) . "                     
                                    alert(xhr.responseText);
            //                        debugger;
                                }                                 
                           })"
                ];
                (new HtmlOf)->CreateJsFunction(null, 'fields', $FCode2, ['el' => 'arg']);
            }
            (new HtmlOf)->CreateJsFunction($this->jsOptions['click'], 'click', $FCode, ['event' => 'arg']);
            // submit click
            (new HtmlOf)->CreateJsFunction("#$AID-modal #$this->submit,#$AID-modal :submit", 'click', $FCodeSubmit, ['event' => 'arg']);
            // Dragged
            $FCode = ["dragElement('$AID-form', '$AID-header')"];
            (new HtmlOf)->CreateJsFunction('window', 'load', $FCode);
        }
        if ($this->jsOptions['modal']['window']) {
            (new HtmlOf)->CreateJsFunction('window', 'click', ["if (event.target.id == '$AID-modal') { $('#$AID-modal').css({display: 'none'}); }"], ['event' => 'arg']);
        }
        if ($this->jsOptions['header']['close']) {
            (new HtmlOf)->CreateJsFunction("$AID-close", 'click', ["$('#$AID-modal').css({display: 'none'})"]);
        }
        return ;
    }

    private function OptionInit($AID) {
        if (isset($this->cssClass)) {
            // add class title
            if (isset($this->title['class'])) $FClassTitle = ArrayHelper::remove($this->title, 'class', CH_FREE); else $FClassTitle = CH_FREE;
            if (isset($this->title['close']['class'])) $FClassClose = ArrayHelper::remove($this->title['close'], 'class', CH_FREE); else $FClassClose = CH_FREE;
            $FOptions = ['class' => trim("$this->cssClass-header $FClassTitle"), 'close' => ['class' => trim("$this->cssClass-close $FClassClose")]];
            if (isset($this->title)) {
                if (is_array($this->title)) $this->title = array_replace_recursive($this->title, $FOptions); else $this->title = array_merge($FOptions, ['label' => $this->title]);
            } else $this->title = $FOptions;
            // add class content
            if (isset($this->content['class'])) $FClassContent = ArrayHelper::remove($this->content, 'class', CH_FREE); else $FClassContent = CH_FREE;
            $FOptions = ['class' => trim("$this->cssClass-content $FClassContent")];
            if (isset($this->content)) {
                if (is_array($this->content)) $this->content = array_replace_recursive($this->content, $FOptions); else $this->content = array_merge($FOptions, ['label' => $this->content]);
            } else $this->content = $FOptions;
            // add class modal
            if (isset($this->modalOptions['class'])) $FClassModal = ArrayHelper::remove($this->modalOptions, 'class', CH_FREE); else $FClassModal = CH_FREE;
            $FOptions = ['class' => trim("$this->cssClass-modal $FClassModal")];
            if (isset($this->modalOptions)) {
                if (is_array($this->modalOptions)) $this->modalOptions = array_replace_recursive($this->modalOptions, $FOptions); else $this->modalOptions = $FOptions;
            } else $this->modalOptions = $FOptions;
            // add class form
            if (isset($this->formOptions['class'])) $FClassForm = ArrayHelper::remove($this->formOptions, 'class', CH_FREE); else $FClassForm = CH_FREE;
            $FOptions = ['class' => trim("$this->cssClass-form $FClassForm")];
            if (isset($this->formOptions)) {
                if (is_array($this->formOptions)) $this->formOptions = array_replace_recursive($this->formOptions, $FOptions); else $this->formOptions = $FOptions;
            } else $this->formOptions = $FOptions;
        }
    }

}
