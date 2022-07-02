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
            $FResult = ALGOL::SystemOf()->Values();
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
                elseif (in_array($FValueUp, self::C_MYSQL_OPERATORS)) $FOperant = $FValueUp;
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
        if (isset($AQuery)) $FResult = $AQuery; else $FResult = self::useTable($ATableNames)::find();
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

    public function Filter($ATableName, $AValues, &$AResult, $AColumns = "*", $ANumRows = null, $AFormat = null, $AFormatClearSubArray = true, $AValueFromString = null, $AOrder = null, $AGroup = null, $AJoin = null, $AJSONParseField = null, $ACond = CH_AND_TEXT) {
        $AResult = null;
        $FResult = $this->Builder($ATableName, $AColumns, $AJoin, $AValues, $AGroup, null, $AOrder, $ANumRows, $ACond);
        if ($FResult) {
            if ($ANumRows === 1) $AResult = $FResult->asArray()->one(); else $AResult = $FResult->asArray()->all();
            if ($AResult) {
                // Get JSON parsed
                if ((new StrOf)->Length($AJSONParseField) > 0) $AResult = (new ArrayOf)->FromJSON($AResult, $AJSONParseField);
                // Get format
                if (($ANumRows <> 1) and !is_null($AFormat)) {
                    foreach ($AResult as $FKey => $FValue) {
                        if (is_array($FValue)) {
                            $AResult[$FKey] = (new StrOf)->Replace($AFormat, array_keys($FValue), array_values($FValue));
                            if ((new DefaultOf)->TypeCheck($FKey)) $AResult[$FKey] = (new StrOf)->Replace($AResult[$FKey], CH_NUMBER, $FKey + 1);
                            if (($AValueFromString === true) or ((new ArrayOf)->Length($AValueFromString) > 0)) $AResult[$FKey] = (new DefaultOf)->ValueFromString($AResult[$FKey], (new DefaultOf)->ValueCheck($AValueFromString[0], 2), (new DefaultOf)->ValueCheck($AValueFromString[1], CH_FREE));
                            if (!$AFormatClearSubArray) $AResult[$FKey] = [$AResult[$FKey]];
                        }
                    }
                }
            }
        }
        return (new ArrayOf)->Length($AResult) > 0;
    }

    public function FilterOne($ATableName, $AValues, &$AResult, $AColumns = "*", $AOrder = null, $AGroup = null, $AJoin = null, $AJSONParseField = null, $ACond = CH_AND_TEXT) {
        return $this->Filter($ATableName, $AValues, $AResult, $AColumns, 1, null, true, null, $AOrder, $AGroup, $AJoin, $AJSONParseField, $ACond);
    }

    public function Append($ATableName, $AValues, &$AResult) {
        $AResult = self::useTable($ATableName);
        foreach ($AValues as $FKey => $FValue) {
            $AResult->setAttribute($FKey, $FValue);
        }
        return $AResult->save();
    }
}

// Const ToGo
const TG_Get = "TG_Get";
const TG_Post = "TG_Post";
const TG_PostForm = "TG_PostForm";

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

    public function ToGo($AValue, $AUrl, $AParam = null, $AType = 'link', $AClass = null, $APost = TG_Get, $AHttps = false) {
        $FResult = CH_FREE;
        if (!ALGOL::StrOf()->Empty([$AValue, $AUrl])) {
            // Get Param
            $FValue = Html::encode($AValue);
            $FUrl = [$AUrl];
            $FOptions = [];
            if (!is_null($AClass)) $FOptions['class'] = $AClass;
            // Check Post
            if ($APost === TG_PostForm) {
                $FResult = Html::beginForm($FUrl, 'post', ['id' => 1]);
                if (ALGOL::ArrayOf()->Empty($AParam)) {
                    $FResult .= html::hiddenInput(ALGOL::DefaultOf()->ValueCheck(ALGOL::ArrayOf()->First($AParam), 'default'), $FValue);
                } else {
                    foreach ($AParam as $FKey => $FItem) {
                        $FResult .= html::hiddenInput($FKey, $FItem);
                    }
                }
                $FOptions['onclick'] = 'this.parentNode.submit();';
                $FResult .= Html::endForm() .
                    Html::a($FValue, null, $FOptions);
            } else {
                // Get Param
                if ($AHttps) $FUrl[] = 'https';
                // Check post
                if ($APost === TG_Post) {
                    $FOptions['data-method'] = 'POST';
                    if (!is_null($AParam)) $FOptions['data-params'] = $AParam;
                } else {
                    $FUrl = ALGOL::ArrayOf()->Of(AO_Merge, $FUrl, $AParam);
                }
                // Check url
                if (ALGOL::StrOf()->Same($AUrl, 'home')) {
                    $FUrl = \yii\helpers\Url::home();
                } elseif (ALGOL::StrOf()->Same($AUrl, 'current')) {
                    $FUrl = \yii\helpers\Url::current();
                }/* else {
                $FUrl = \yii\helpers\Url::to($FUrl);
            }*/
                // Check type
                if (ALGOL::StrOf()->Same($AType, 'link')) {
                    $FResult = Html::a($FValue, $FUrl, $FOptions);
                } elseif (ALGOL::StrOf()->Same($AType, 'mailto')) {
                    $FResult = Html::mailto($FValue, $AUrl, $FOptions);
                } elseif (ALGOL::StrOf()->Same($AType, 'button')) {
                    $FResult = Html::button($FValue, $FOptions);
                } elseif (ALGOL::StrOf()->Same($AType, 'submit')) {
                    $FResult = Html::submitButton($FValue, $FOptions);
                } elseif (ALGOL::StrOf()->Same($AType, 'reset')) {
                    $FResult = Html::resetButton($FValue, $FOptions);
                }
            }
        }
        return $FResult;
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
        $FOnClick = ALGOL::ArrayOf()->FromFunction($this->onclick, $model, $key, $index);
        if (ALGOL::ArrayOf()->Length($FOnClick) > 1) {
            if (ALGOL::StrOf()->Length($FOnClick['value'], true) > 0) {
                $AResult['content'] = $FOnClick['value'];
                if (ALGOL::DefaultOf()->TypeCheck($AResult['content'], DTC_HTML)) $this->format = 'raw';
            }
            if (ALGOL::StrOf()->Length($FOnClick['url'], true) > 0) {
                $FOptions = ALGOL::ArrayOf()->FromFunction($this->contentOptions, $model, $key, $index);
                if (ALGOL::DefaultOf()->ValueCheck($FOnClick['expand'], true)) {
                    if (is_null($this->url_expand)) {
                        $this->url_expand = $FOnClick['url'];
                        $this->regScript(true);
                    }
                    $FOptions['data-row_id'] = $this->normalizeRowID($key);
                    $FOptions['data-col_id'] = $this->column_id;
                    $FClass = self::EXPANDED_CLASS . CH_MINUS . $this->column_id;
                } else {
                    if (is_null($this->url_goto)) {
                        $this->url_goto = $FOnClick['url'];
                        $this->regScript(false);
                    }
                    $FClass = self::EXPANDED_CLASS . CH_MINUS . self::REDIRECT;
                }
                $FOptions['class'] = $FClass . (isset($FOptions['class']) ? " {$FOptions['class']}" : CH_FREE);
                if (ALGOL::ArrayOf()->Length($FOnClick['data']) > 0) $FOptions['data-info'] = $FOnClick['data']; else $FOptions['data-info'] = is_array($key) ? $key : ['id' => $key];
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
            $FClass = $FClass = self::EXPANDED_CLASS . CH_MINUS . $this->column_id;
            $FOptions = Json::encode(['url' => $this->url_expand,
                'countColumns' => count($this->grid->columns),
                'enableCache' => (bool)$this->enableCache,
                'loading' => $this->loading,
                'hideEffect' => 'fadeOut',
                'showEffect' => 'fadeIn',
                'redirect' => false]);
        } else {
            $FClass = $FClass = self::EXPANDED_CLASS . CH_MINUS . self::REDIRECT;
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
    public $depends = ['yii\web\JqueryAsset'];
}