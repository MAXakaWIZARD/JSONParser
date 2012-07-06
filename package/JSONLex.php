<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'JLexToken.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'JLexBase.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'JSONParser.php';

/**
 *
 */
class JSONLex extends JLexBase
{
    protected $_countChars = true;
    protected $_countLines = true;

    protected $_auxBuffer = '';

    const LEX_STATE_INITIAL = 0;
    const LEX_STATE_STRING_BEGIN = 1;

    static $stateDtrans = array(0, 36);

    static $_acpt
        = array(
            /* 0 */
            self::YY_NOT_ACCEPT,
            /* 1 */
            self::YY_NO_ANCHOR,
            /* 2 */
            self::YY_NO_ANCHOR,
            /* 3 */
            self::YY_NO_ANCHOR,
            /* 4 */
            self::YY_NO_ANCHOR,
            /* 5 */
            self::YY_NO_ANCHOR,
            /* 6 */
            self::YY_NO_ANCHOR,
            /* 7 */
            self::YY_NO_ANCHOR,
            /* 8 */
            self::YY_NO_ANCHOR,
            /* 9 */
            self::YY_NO_ANCHOR,
            /* 10 */
            self::YY_NO_ANCHOR,
            /* 11 */
            self::YY_NO_ANCHOR,
            /* 12 */
            self::YY_NO_ANCHOR,
            /* 13 */
            self::YY_NO_ANCHOR,
            /* 14 */
            self::YY_NO_ANCHOR,
            /* 15 */
            self::YY_NO_ANCHOR,
            /* 16 */
            self::YY_NO_ANCHOR,
            /* 17 */
            self::YY_NO_ANCHOR,
            /* 18 */
            self::YY_NO_ANCHOR,
            /* 19 */
            self::YY_NO_ANCHOR,
            /* 20 */
            self::YY_NO_ANCHOR,
            /* 21 */
            self::YY_NO_ANCHOR,
            /* 22 */
            self::YY_NO_ANCHOR,
            /* 23 */
            self::YY_NOT_ACCEPT,
            /* 24 */
            self::YY_NO_ANCHOR,
            /* 25 */
            self::YY_NOT_ACCEPT,
            /* 26 */
            self::YY_NOT_ACCEPT,
            /* 27 */
            self::YY_NOT_ACCEPT,
            /* 28 */
            self::YY_NOT_ACCEPT,
            /* 29 */
            self::YY_NOT_ACCEPT,
            /* 30 */
            self::YY_NOT_ACCEPT,
            /* 31 */
            self::YY_NOT_ACCEPT,
            /* 32 */
            self::YY_NOT_ACCEPT,
            /* 33 */
            self::YY_NOT_ACCEPT,
            /* 34 */
            self::YY_NOT_ACCEPT,
            /* 35 */
            self::YY_NOT_ACCEPT,
            /* 36 */
            self::YY_NOT_ACCEPT,
            /* 37 */
            self::YY_NOT_ACCEPT,
            /* 38 */
            self::YY_NOT_ACCEPT
        );

    /**
     * @var array
     */
    protected $_cMap
        = array(
            2, 2, 2, 2, 2, 2, 2, 2, 2, 25, 25, 2, 2, 25, 2, 2, 2, 2, 2, 2,
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 25, 2, 1, 2, 2, 2, 2, 2,
            2, 2, 2, 13, 23, 9, 11, 2, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 24, 2,
            2, 2, 2, 2, 2, 2, 2, 2, 2, 12, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2,
            2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 21, 3, 22, 2, 2, 2, 16, 4, 2,
            2, 15, 5, 2, 2, 2, 2, 2, 17, 2, 6, 2, 2, 2, 7, 18, 8, 14, 2, 2,
            2, 2, 2, 19, 2, 20);

    static $_rMap
        = array(
            0, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 4, 1, 1, 1, 5, 1, 1, 1, 1,
            1, 1, 1, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 7, 18, 19, 20,);

    static $_nxt
        = array(
            array(
                1, 2, -1, -1, -1, 23, 25, -1, 26, 27, 3, -1, -1, -1, -1, -1, -1, -1, -1, 4,
                5, 6, 7, 8, 9, 10,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 3, 30, 31, -1, -1, 31, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, 10,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 11, -1, 31, -1, -1, 31, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, 15, -1, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15,
                15, 15, 15, 15, 15, 15,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 28, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 24, -1, -1, -1, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 38, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, 29, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 3, -1, -1, -1, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 32, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 34, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 11, -1, -1, -1, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, 35, 24, -1, -1, 35, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 34, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 12, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 13, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                1, 14, 15, 37, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15,
                15, 15, 15, 15, 15, 15,
            ),
            array(
                -1, 16, -1, 17, 18, 19, 20, 21, 22, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
            array(
                -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 33, -1, -1,
                -1, -1, -1, -1, -1, -1,
            ),
        );

    /**
     * @param $stream
     */
    public function __construct($stream)
    {
        parent::__construct($stream);
        $this->_lexicalState = self::LEX_STATE_INITIAL;

        /*
        $cnt = count($this->_yy_cmap);
        $flag = false;
        $counter = 0;
        for ($i = 126; $i < $cnt; $i++) {
            if($this->_yy_cmap[$i] !== 2) {
                $idx = $i;
                $val = $this->_yy_cmap[$i];
                $flag = true;
                break;
            } else {
                $counter++;
            }
        }
        */

        //fill array
        for ($i = 1; $i <= 65410; $i++) {
            $this->_cMap[] = 2;
        }
        $this->_cMap[] = 0;
        $this->_cMap[] = 0;

        //var_dump(count($this->_cMap));
        //count = 65538

        /*
        var_dump($flag);
        echo "idx: ";
        var_dump($i);
        echo "val: ";
        var_dump($val);
        echo "counter: ";
        var_dump($counter);
        var_dump($cnt);
        */
        //exit;
    }

    /**
     * @return JLexToken|null
     * @throws Exception
     */
    public function nextToken()
    {
        $anchor = self::YY_NO_ANCHOR;
        $state = self::$stateDtrans[$this->_lexicalState];
        $nextState = self::YY_NO_STATE;
        $lastAcceptState = self::YY_NO_STATE;
        $initial = true;

        $this->_markStart();
        $accept = self::$_acpt[$state];
        if (self::YY_NOT_ACCEPT != $accept) {
            $lastAcceptState = $state;
            $this->_markEnd();
        }

        while (true) {
            if ($initial && $this->_atBol) {
                $lookahead = self::YY_BOL;
            } else {
                $lookahead = $this->_advance();
            }
            $nextState = self::$_nxt[self::$_rMap[$state]][$this->_cMap[$lookahead]];
            if (self::YY_EOF == $lookahead && true == $initial) {
                return null;
            }
            if (self::YY_F != $nextState) {
                $state = $nextState;
                $initial = false;
                $accept = self::$_acpt[$state];
                if (self::YY_NOT_ACCEPT != $accept) {
                    $lastAcceptState = $state;
                    $this->_markEnd();
                }
            } else {
                if (self::YY_NO_STATE == $lastAcceptState) {
                    throw new Exception("Lexical Error: Unmatched Input.");
                } else {
                    $anchor = self::$_acpt[$lastAcceptState];
                    if (0 != (self::YY_END & $anchor)) {
                        $this->_moveEnd();
                    }
                    $this->_toMark();
                    switch ($lastAcceptState) {
                        case 1:

                        case -2:
                            break;
                        case 2:
                            $this->_auxBuffer = '';
                            $this->_begin(self::LEX_STATE_STRING_BEGIN);
                        case -3:
                            break;
                        case 3:
                            return $this->createToken(JSONParser::TK_NUMBER);
                        case -4:
                            break;
                        case 4:
                            return $this->createToken(JSONParser::TK_LEFT_BRACE);
                        case -5:
                            break;
                        case 5:
                            return $this->createToken(JSONParser::TK_RIGHT_BRACE);
                        case -6:
                            break;
                        case 6:
                            return $this->createToken(JSONParser::TK_LEFT_SQUARE);
                        case -7:
                            break;
                        case 7:
                            return $this->createToken(JSONParser::TK_RIGHT_SQUARE);
                        case -8:
                            break;
                        case 8:
                            return $this->createToken(JSONParser::TK_COMMA);
                        case -9:
                            break;
                        case 9:
                            return $this->createToken(JSONParser::TK_COLON);
                        case -10:
                            break;
                        case 10:
                        case -11:
                            break;
                        case 11:
                            return $this->createToken(JSONParser::TK_NUMBER);
                        case -12:
                            break;
                        case 12:
                            return $this->createToken(JSONParser::TK_NULL);
                        case -13:
                            break;
                        case 13:
                            return $this->createToken(JSONParser::TK_BOOL);
                        case -14:
                            break;
                        case 14:
                            $this->_begin(self::LEX_STATE_INITIAL);
                            return $this->createToken(JSONParser::TK_STRING, $this->_auxBuffer);
                        case -15:
                            break;
                        case 15:
                            $this->_auxBuffer .= $this->_getText();
                        case -16:
                            break;
                        case 16:
                            $this->_auxBuffer .= '"';
                        case -17:
                            break;
                        case 17:
                            $this->_auxBuffer .= '\\';
                        case -18:
                            break;
                        case 18:
                            $this->_auxBuffer .= "\b";
                        case -19:
                            break;
                        case 19:
                            $this->_auxBuffer .= "\f";
                        case -20:
                            break;
                        case 20:
                            $this->_auxBuffer .= "\n";
                        case -21:
                            break;
                        case 21:
                            $this->_auxBuffer .= "\r";
                        case -22:
                            break;
                        case 22:
                            $this->_auxBuffer .= "\t";
                        case -23:
                            break;
                        case 24:
                            return $this->createToken(JSONParser::TK_NUMBER);
                        case -24:
                            break;
                        default:
                            $this->_triggerError('INTERNAL', false);
                        case -1:
                    }
                    $initial = true;
                    $state = self::$stateDtrans[$this->_lexicalState];
                    $nextState = self::YY_NO_STATE;
                    $lastAcceptState = self::YY_NO_STATE;
                    $this->_markStart();
                    $accept = self::$_acpt[$state];
                    if (self::YY_NOT_ACCEPT != $accept) {
                        $lastAcceptState = $state;
                        $this->_markEnd();
                    }
                }
            }
        }
    }
}