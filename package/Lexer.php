<?php
/**
 *
 */

namespace Json;

/**
 *
 */
class Lexer extends LexerBase
{
    protected $_countChars = true;
    protected $_countLines = true;

    protected $_auxBuffer = '';

    /**
     * @var array
     */
    protected $_stateDtrans = array(0, 36);

    /**
     * @var array
     */
    protected $_acpt
        = array(
            /* 0 */
            self::NOT_ACCEPT,
            /* 1 */
            self::NO_ANCHOR,
            /* 2 */
            self::NO_ANCHOR,
            /* 3 */
            self::NO_ANCHOR,
            /* 4 */
            self::NO_ANCHOR,
            /* 5 */
            self::NO_ANCHOR,
            /* 6 */
            self::NO_ANCHOR,
            /* 7 */
            self::NO_ANCHOR,
            /* 8 */
            self::NO_ANCHOR,
            /* 9 */
            self::NO_ANCHOR,
            /* 10 */
            self::NO_ANCHOR,
            /* 11 */
            self::NO_ANCHOR,
            /* 12 */
            self::NO_ANCHOR,
            /* 13 */
            self::NO_ANCHOR,
            /* 14 */
            self::NO_ANCHOR,
            /* 15 */
            self::NO_ANCHOR,
            /* 16 */
            self::NO_ANCHOR,
            /* 17 */
            self::NO_ANCHOR,
            /* 18 */
            self::NO_ANCHOR,
            /* 19 */
            self::NO_ANCHOR,
            /* 20 */
            self::NO_ANCHOR,
            /* 21 */
            self::NO_ANCHOR,
            /* 22 */
            self::NO_ANCHOR,
            /* 23 */
            self::NOT_ACCEPT,
            /* 24 */
            self::NO_ANCHOR,
            /* 25 */
            self::NOT_ACCEPT,
            /* 26 */
            self::NOT_ACCEPT,
            /* 27 */
            self::NOT_ACCEPT,
            /* 28 */
            self::NOT_ACCEPT,
            /* 29 */
            self::NOT_ACCEPT,
            /* 30 */
            self::NOT_ACCEPT,
            /* 31 */
            self::NOT_ACCEPT,
            /* 32 */
            self::NOT_ACCEPT,
            /* 33 */
            self::NOT_ACCEPT,
            /* 34 */
            self::NOT_ACCEPT,
            /* 35 */
            self::NOT_ACCEPT,
            /* 36 */
            self::NOT_ACCEPT,
            /* 37 */
            self::NOT_ACCEPT,
            /* 38 */
            self::NOT_ACCEPT
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

    /**
     * @var array
     */
    protected $_rMap
        = array(
            0, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 4, 1, 1, 1, 5, 1, 1, 1, 1,
            1, 1, 1, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 7, 18, 19, 20,);

    /**
     * @var array
     */
    protected $_nxt
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
        /*
        for ($i = 1; $i <= 65410; $i++) {
            $this->_cMap[] = 2;
        }
        $this->_cMap[] = 0;
        $this->_cMap[] = 0;
        */

        //var_dump(count($this->_cMap));
        //count = 65538
    }

    /**
     * @return Token|null
     * @throws \Exception
     */
    public function nextToken()
    {
        $anchor = self::NO_ANCHOR;
        $state = $this->_stateDtrans[$this->_lexicalState];
        $nextState = self::NO_STATE;
        $lastAcceptState = self::NO_STATE;
        $initial = true;

        $this->_markStart();
        $accept = $this->_acpt[$state];
        if (self::NOT_ACCEPT != $accept) {
            $lastAcceptState = $state;
            $this->_markEnd();
        }

        while (true) {
            if ($initial && $this->_atBol) {
                $lookahead = self::BOL;
            } else {
                $lookahead = $this->_advance();
            }
            $nextState = $this->_getNextState($state, $lookahead);
            if (self::EOF == $lookahead && true == $initial) {
                return null;
            }
            if (self::F != $nextState) {
                $state = $nextState;
                $initial = false;
                $accept = $this->_acpt[$state];
                if (self::NOT_ACCEPT != $accept) {
                    $lastAcceptState = $state;
                    $this->_markEnd();
                }
            } else {
                if (self::NO_STATE == $lastAcceptState) {
                    throw new \Exception("Lexical Error: Unmatched Input.");
                } else {
                    $anchor = $this->_acpt[$lastAcceptState];
                    if (0 != (self::END & $anchor)) {
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
                            return $this->createToken(Parser::TK_NUMBER);
                        case -4:
                            break;
                        case 4:
                            return $this->createToken(Parser::TK_LEFT_BRACE);
                        case -5:
                            break;
                        case 5:
                            return $this->createToken(Parser::TK_RIGHT_BRACE);
                        case -6:
                            break;
                        case 6:
                            return $this->createToken(Parser::TK_LEFT_SQUARE);
                        case -7:
                            break;
                        case 7:
                            return $this->createToken(Parser::TK_RIGHT_SQUARE);
                        case -8:
                            break;
                        case 8:
                            return $this->createToken(Parser::TK_COMMA);
                        case -9:
                            break;
                        case 9:
                            return $this->createToken(Parser::TK_COLON);
                        case -10:
                            break;
                        case 10:
                        case -11:
                            break;
                        case 11:
                            return $this->createToken(Parser::TK_NUMBER);
                        case -12:
                            break;
                        case 12:
                            return $this->createToken(Parser::TK_NULL);
                        case -13:
                            break;
                        case 13:
                            return $this->createToken(Parser::TK_BOOL);
                        case -14:
                            break;
                        case 14:
                            $this->_begin(self::LEX_STATE_INITIAL);
                            return $this->createToken(Parser::TK_STRING, $this->_auxBuffer);
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
                            return $this->createToken(Parser::TK_NUMBER);
                        case -24:
                            break;
                        default:
                            $this->_triggerError('INTERNAL', false);
                        case -1:
                    }

                    $initial = true;
                    $state = $this->_stateDtrans[$this->_lexicalState];
                    $nextState = self::NO_STATE;
                    $lastAcceptState = self::NO_STATE;
                    $this->_markStart();

                    $accept = $this->_acpt[$state];
                    if (self::NOT_ACCEPT != $accept) {
                        $lastAcceptState = $state;
                        $this->_markEnd();
                    }
                }
            }
        }
    }

    /**
     * @param $state
     * @param $lookahead
     *
     * @return mixed
     */
    protected function _getNextState($state, $lookahead)
    {
        if ($lookahead <= 125) {
            $cMapValue = $this->_cMap[$lookahead];
        } elseif ($lookahead < self::EOF - 1) {
            $cMapValue = 2;
        } else {
            $cMapValue = 0;
        }

        return $this->_nxt[$this->_rMap[$state]][$cMapValue];
    }
}