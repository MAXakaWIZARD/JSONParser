<?php
/*
  Copyright 2006 Wez Furlong, OmniTI Computer Consulting, Inc.
  Based on JLex which is:

  JLEX COPYRIGHT NOTICE, LICENSE, AND DISCLAIMER
  Copyright 1996-2000 by Elliot Joel Berk and C. Scott Ananian

  Permission to use, copy, modify, and distribute this software and its
  documentation for any purpose and without fee is hereby granted,
  provided that the above copyright notice appear in all copies and that
  both the copyright notice and this permission notice and warranty
  disclaimer appear in supporting documentation, and that the name of
  the authors or their employers not be used in advertising or publicity
  pertaining to distribution of the software without specific, written
  prior permission.

  The authors and their employers disclaim all warranties with regard to
  this software, including all implied warranties of merchantability and
  fitness. In no event shall the authors or their employers be liable
  for any special, indirect or consequential damages or any damages
  whatsoever resulting from loss of use, data or profits, whether in an
  action of contract, negligence or other tortious action, arising out
  of or in connection with the use or performance of this software.
  **************************************************************
*/

namespace Json;

/**
 *
 */
class Lexer
{
    const BUFFER_SIZE = 8192;
    const F = -1;
    const NO_STATE = -1;
    const NOT_ACCEPT = 0;
    const START = 1;
    const END = 2;
    const NO_ANCHOR = 4;

    const LINE_START = 65536;
    const EOF = 65537;

    const LEX_STATE_INITIAL = 0;
    const LEX_STATE_STRING_BEGIN = 1;

    protected $_reader;
    protected $_streamFilename = null;

    /**
     * @var string
     */
    protected $_buffer;

    protected $_auxBuffer = '';

    protected $_bufferRead;
    protected $_bufferIndex;
    protected $_bufferStart;
    protected $_bufferEnd;

    protected $_char = 0;
    protected $_col = 0;
    protected $_line = 0;

    protected $_atStartOfLine;

    protected $_lexicalState;

    protected $_lastWasCr = false;

    protected $_countLines = true;
    protected $_countChars = true;

    /**
     * @var array
     */
    static $errorStrings
        = array(
            'INTERNAL' => "Error: internal error.\n",
            'MATCH'    => "Error: Unmatched input.\n"
        );

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
            2, 2, 2, 19, 2, 20
        );

    /**
     * @var array
     */
    protected $_rMap
        = array(
            0, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 4, 1, 1, 1, 5, 1, 1, 1, 1,
            1, 1, 1, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 7, 18, 19, 20,
        );

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
        $this->_lexicalState = self::LEX_STATE_INITIAL;

        $this->_reader = $stream;
        $meta = stream_get_meta_data($stream);
        if (isset($meta['uri'])) {
            $this->_streamFilename = $meta['uri'];
        } else {
            $this->_streamFilename = '<<input>>';
        }

        $this->_buffer = "";
        $this->_bufferRead = 0;
        $this->_bufferIndex = 0;
        $this->_bufferStart = 0;
        $this->_bufferEnd = 0;
        $this->_char = 0;
        $this->_line = 1;
        $this->_atStartOfLine = true;

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
     * @param $state
     */
    protected function _begin($state)
    {
        $this->_lexicalState = $state;
    }

    /**
     * @return int
     */
    protected function _advance()
    {
        if ($this->_bufferIndex < $this->_bufferRead) {
            if (!isset($this->_buffer[$this->_bufferIndex])) {
                return self::EOF;
            }
            return ord($this->_buffer[$this->_bufferIndex++]);
        }

        if ($this->_bufferStart != 0) {
            /* shunt */
            $j = $this->_bufferRead - $this->_bufferStart;
            $this->_buffer = substr($this->_buffer, $this->_bufferStart, $j);
            $this->_bufferEnd -= $this->_bufferStart;
            $this->_bufferStart = 0;
            $this->_bufferRead = $j;
            $this->_bufferIndex = $j;

            $data = fread($this->_reader, self::BUFFER_SIZE);
            $dataLength = strlen($data);
            if ($data === false || !$dataLength) {
                return self::EOF;
            }
            $this->_buffer .= $data;
            $this->_bufferRead += $dataLength;
        }

        while ($this->_bufferIndex >= $this->_bufferRead) {
            $data = fread($this->_reader, self::BUFFER_SIZE);
            $dataLength = strlen($data);
            if ($data === false || !$dataLength) {
                return self::EOF;
            }
            $this->_buffer .= $data;
            $this->_bufferRead += $dataLength;
        }

        return ord($this->_buffer[$this->_bufferIndex++]);
    }

    /**
     *
     */
    protected function _moveEnd()
    {
        if ($this->_bufferEnd > $this->_bufferStart) {
            $lastChar = $this->_buffer[$this->_bufferEnd - 1];
            if ($lastChar == "\n" || $lastChar == "\r") {
                $this->_bufferEnd--;
            }
        }
    }

    /**
     *
     */
    protected function _markStart()
    {
        /*
        for ($i = $this->_bufferStart; $i < $this->_bufferIndex; ++$i) {
            if ("\n" == $this->_buffer[$i] && !$this->_lastWasCr) {
                ++$this->_line;
                $this->_col = 0;
            } elseif ("\r" == $this->_buffer[$i]) {
                ++$this->_line;
                $this->_col = 0;
                $this->_lastWasCr = true;
            } else {
                $this->_lastWasCr = false;
            }
        }

        $this->_char += $this->_bufferIndex - $this->_bufferStart;
        $this->_col += $this->_bufferIndex - $this->_bufferStart;
        */

        $this->_bufferStart = $this->_bufferIndex;
    }

    /**
     *
     */
    protected function _markEnd()
    {
        $this->_bufferEnd = $this->_bufferIndex;
    }

    /**
     *
     */
    protected function _toMark()
    {
        #echo "_toMark: setting buffer index to ", $this->_bufferEnd, "\n";
        $this->_bufferIndex = $this->_bufferEnd;
        $this->_atStartOfLine = ($this->_bufferEnd > $this->_bufferStart)
            && ("\r" == $this->_buffer[$this->_bufferEnd - 1]
                || "\n" == $this->_buffer[$this->_bufferEnd - 1]
                || 2028 /* unicode LS */ == $this->_buffer[$this->_bufferEnd - 1]
                || 2029 /* unicode PS */ == $this->_buffer[$this->_bufferEnd - 1]);
    }

    /**
     * @return string
     */
    protected function _getText()
    {
        return substr(
            $this->_buffer, $this->_bufferStart,
            $this->_bufferEnd - $this->_bufferStart
        );
    }

    /**
     * @return int
     */
    protected function _getLength()
    {
        return $this->_bufferEnd - $this->_bufferStart;
    }

    /**
     * @param $code
     * @param $fatal
     *
     * @throws \Exception
     */
    protected function _triggerError($code, $fatal = false)
    {
        print self::$errorStrings[$code];
        flush();

        if ($fatal) {
            throw new \Exception("Lexer fatal error " . self::$errorStrings[$code]);
        }
    }

    /**
     * creates an annotated token
     *
     * @param null $type
     * @param null $value
     *
     * @return Token
     */
    public function createToken($type = null, $value = null)
    {
        if (is_null($type)) {
            $type = $this->_getText();
        }

        $token = new Token($type);
        $this->annotateToken($token, $value);

        return $token;
    }

    /**
     * annotates a token with a value and source positioning
     *
     * @param Token     $token
     * @param null      $value
     */
    public function annotateToken(Token $token, $value = null)
    {
        $token->value = is_null($value) ? $this->_getText() : $value;
        $token->col = $this->_col;
        $token->line = $this->_line;
        $token->filename = $this->_streamFilename;
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

            if ($initial && $this->_atStartOfLine) {
                $lookahead = self::LINE_START;
            } else {
                $lookahead = $this->_advance();
            }

            if (self::EOF == $lookahead && $initial) {
                return null;
            }

            $nextState = $this->_getNextState($state, $lookahead);

            if (self::F == $nextState) {

                if (self::NO_STATE == $lastAcceptState) {
                    throw new \Exception("Lexical Error: Unmatched Input.");
                }

                $anchor = $this->_acpt[$lastAcceptState];
                if (0 != (self::END & $anchor)) {
                    $this->_moveEnd();
                }

                $this->_toMark();

                //echo $lastAcceptState . '<br/>';
                //$this->usedStatesMap[$lastAcceptState]++;

                if ($lastAcceptState > -24) {
                    switch ($lastAcceptState) {
                        case 1:
                            break;
                        case 2:
                            $this->_auxBuffer = '';
                            $this->_begin(self::LEX_STATE_STRING_BEGIN);
                            break;
                        case 3:
                            return $this->createToken(Parser::TK_NUMBER);
                        case 4:
                            return $this->createToken(Parser::TK_LEFT_BRACE);
                        case 5:
                            return $this->createToken(Parser::TK_RIGHT_BRACE);
                        case 6:
                            return $this->createToken(Parser::TK_LEFT_SQUARE);
                        case 7:
                            return $this->createToken(Parser::TK_RIGHT_SQUARE);
                        case 8:
                            return $this->createToken(Parser::TK_COMMA);
                        case 9:
                            return $this->createToken(Parser::TK_COLON);
                        case 10:
                            break;
                        case 11:
                            return $this->createToken(Parser::TK_NUMBER);
                        case 12:
                            return $this->createToken(Parser::TK_NULL);
                        case 13:
                            return $this->createToken(Parser::TK_BOOL);
                        case 14:
                            $this->_begin(self::LEX_STATE_INITIAL);
                            return $this->createToken(Parser::TK_STRING, $this->_auxBuffer);
                        case 15:
                            $this->_auxBuffer .= $this->_getText();
                            break;
                        case 16:
                            $this->_auxBuffer .= '"';
                            break;
                        case 17:
                            $this->_auxBuffer .= '\\';
                            break;
                        case 18:
                            $this->_auxBuffer .= "\b";
                            break;
                        case 19:
                            $this->_auxBuffer .= "\f";
                            break;
                        case 20:
                            $this->_auxBuffer .= "\n";
                            break;
                        case 21:
                            $this->_auxBuffer .= "\r";
                            break;
                        case 22:
                            $this->_auxBuffer .= "\t";
                            break;
                        case 24:
                            return $this->createToken(Parser::TK_NUMBER);
                        default:
                            $this->_triggerError('INTERNAL');
                    }
                }

                $initial = true;
                $state = $this->_stateDtrans[$this->_lexicalState];

                $nextState = self::NO_STATE;
                $lastAcceptState = self::NO_STATE;

                $this->_markStart();
            } else {
                $initial = false;
                $state = $nextState;
            }

            $accept = $this->_acpt[$state];
            if (self::NOT_ACCEPT != $accept) {
                $lastAcceptState = $state;
                $this->_markEnd();
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