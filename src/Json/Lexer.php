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
    const BUFFER_SIZE = 16384;

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

    protected $_dataStream;
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

    protected $_atStartOfLine;

    protected $_lexicalState;

    protected $_lastWasCr = false;

    protected $_countLines = true;
    protected $_countChars = true;

    /**
     * @var array
     */
    static $errorStrings = array(
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
    protected $_acpt = array(
        /* 0 */ self::NOT_ACCEPT,
        /* 1 */ self::NO_ANCHOR,
        /* 2 */ self::NO_ANCHOR,
        /* 3 */ self::NO_ANCHOR,
        /* 4 */ self::NO_ANCHOR,
        /* 5 */ self::NO_ANCHOR,
        /* 6 */ self::NO_ANCHOR,
        /* 7 */ self::NO_ANCHOR,
        /* 8 */ self::NO_ANCHOR,
        /* 9 */ self::NO_ANCHOR,
        /* 10 */ self::NO_ANCHOR,
        /* 11 */ self::NO_ANCHOR,
        /* 12 */ self::NO_ANCHOR,
        /* 13 */ self::NO_ANCHOR,
        /* 14 */ self::NO_ANCHOR,
        /* 15 */ self::NO_ANCHOR,
        /* 16 */ self::NO_ANCHOR,
        /* 17 */ self::NO_ANCHOR,
        /* 18 */ self::NO_ANCHOR,
        /* 19 */ self::NO_ANCHOR,
        /* 20 */ self::NO_ANCHOR,
        /* 21 */ self::NO_ANCHOR,
        /* 22 */ self::NO_ANCHOR,
        /* 23 */ self::NOT_ACCEPT,
        /* 24 */ self::NO_ANCHOR,
        /* 25 */ self::NOT_ACCEPT,
        /* 26 */ self::NOT_ACCEPT,
        /* 27 */ self::NOT_ACCEPT,
        /* 28 */ self::NOT_ACCEPT,
        /* 29 */ self::NOT_ACCEPT,
        /* 30 */ self::NOT_ACCEPT,
        /* 31 */ self::NOT_ACCEPT,
        /* 32 */ self::NOT_ACCEPT,
        /* 33 */ self::NOT_ACCEPT,
        /* 34 */ self::NOT_ACCEPT,
        /* 35 */ self::NOT_ACCEPT,
        /* 36 */ self::NOT_ACCEPT,
        /* 37 */ self::NOT_ACCEPT,
        /* 38 */ self::NOT_ACCEPT
    );

    /**
     * @var array
     */
    protected $_cMap = array(
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
    protected $_rMap = array(
        0, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 4, 1, 1, 1, 5, 1, 1, 1, 1, 1, 1, 1, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 7, 18, 19, 20,
    );

    /**
     * @var array
     */
    protected $_nxt = array(
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
        $this->_setLexicalState(self::LEX_STATE_INITIAL);

        $this->_dataStream = $stream;
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

        $this->_atStartOfLine = true;
    }

    /**
     * @param $state
     */
    protected function _setLexicalState($state)
    {
        $this->_lexicalState = $state;
    }

    /**
     * returns next character code from data buffer
     *
     * @return int
     */
    protected function _advance()
    {
        if ($this->_bufferIndex >= $this->_bufferRead) {
            //unbuffered data needed

            if ($this->_bufferStart != 0) {
                //buffer end reached, cut already processed data
                //$this->advanceUsageMap['branch2']++;

                $bufferRemainLength = $this->_bufferRead - $this->_bufferStart;
                $this->_buffer = substr($this->_buffer, $this->_bufferStart, $bufferRemainLength);
                $this->_bufferEnd -= $this->_bufferStart;
                $this->_bufferStart = 0;
                $this->_bufferRead = $bufferRemainLength;
                $this->_bufferIndex = $bufferRemainLength;
            }

            //read another portion of data
            while ($this->_bufferIndex >= $this->_bufferRead) {
                //$this->advanceUsageMap['branch3']++;

                if (!$this->_readNextDataPortion()) {
                    return self::EOF;
                }
            }
        } else {
            //data is already in buffer
            //$this->advanceUsageMap['branch1']++;

            if (!isset($this->_buffer[$this->_bufferIndex])) {
                return self::EOF;
            }
        }

        return ord($this->_buffer[$this->_bufferIndex++]);
    }

    /**
     * @return bool
     */
    protected function _readNextDataPortion()
    {
        if (feof($this->_dataStream)) {
            //end of file reached
            return false;
        }

        $data = fread($this->_dataStream, self::BUFFER_SIZE);

        //error occured
        if ($data === false) {
            return false;
        }

        $this->_buffer .= $data;
        $this->_bufferRead += strlen($data);

        return true;
    }

    /**
     *
     */
    protected function _moveEnd()
    {
        if ($this->_bufferEnd > $this->_bufferStart) {
            $lastChar = $this->_buffer[$this->_bufferEnd - 1];
            if ($this->_isLineBreakChar($lastChar)) {
                $this->_bufferEnd--;
            }
        }
    }

    /**
     *
     */
    protected function _moveBufferIndexToBufferEnd()
    {
        //echo "_toMark: setting buffer index to ", $this->_bufferEnd, "<br/>";
        $this->_bufferIndex = $this->_bufferEnd;
        $this->_atStartOfLine = $this->_bufferEnd > $this->_bufferStart
            && $this->_isLineBreakChar($this->_buffer[$this->_bufferEnd - 1]);
    }

    /**
     * @param $char
     *
     * @return bool
     */
    protected function _isLineBreakChar($char)
    {
        return "\r" == $char
            || "\n" == $char
            || 2028 /* unicode LS */ == $char
            || 2029 /* unicode PS */ == $char;
    }

    /**
     * @return string
     */
    protected function _getText()
    {
        return substr($this->_buffer, $this->_bufferStart, $this->_bufferEnd - $this->_bufferStart);
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
        echo self::$errorStrings[$code];
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
    protected function _createToken($type = null, $value = null)
    {
        if (is_null($type)) {
            $type = $this->_getText();
        }

        $token = new Token($type);
        $token->value = is_null($value) ? $this->_getText() : $value;
        $token->filename = $this->_streamFilename;

        return $token;
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

        /*
        $accept = $this->_acpt[$state];
        if (self::NOT_ACCEPT != $accept) {
            $lastAcceptState = $state;
            $this->_bufferEnd = $this->_bufferIndex;
        } else {
            $this->_bufferStart = $this->_bufferIndex;
        }
        */

        do {
            $accept = $this->_acpt[$state];
            if (self::NOT_ACCEPT != $accept) {
                $lastAcceptState = $state;
                $this->_bufferEnd = $this->_bufferIndex;
            } else {
                $this->_bufferStart = $this->_bufferIndex;
            }

            if ($initial && $this->_atStartOfLine) {
                $lookahead = self::LINE_START;
            } else {
                $lookahead = $this->_advance();
            }

            if ($lookahead == self::EOF && $initial) {
                //data end reached
                return null;
            }

            $nextState = $this->_getNextState($state, $lookahead);

            if ($nextState == self::F) {

                if ($lastAcceptState == self::NO_STATE) {
                    throw new \Exception("Lexical Error: Unmatched Input.");
                }

                /*
                $anchor = $this->_acpt[$lastAcceptState];
                if (0 != (self::END & $anchor)) {
                    $this->_moveEnd();
                }
                */

                $this->_bufferIndex = $this->_bufferEnd;
                $this->_atStartOfLine = $this->_bufferEnd > $this->_bufferStart
                    && $this->_isLineBreakChar($this->_buffer[$this->_bufferEnd - 1]);

                /*
                if (isset($this->usedStatesMap[$lastAcceptState])) {
                    $this->usedStatesMap[$lastAcceptState]++;
                } else {
                    $this->usedStatesMap[$lastAcceptState] = 1;
                }
                */

                if ($lastAcceptState > -24) {
                    switch ($lastAcceptState) {
                        case 2:
                            $this->_auxBuffer = '';
                            $this->_setLexicalState(self::LEX_STATE_STRING_BEGIN);
                            break;
                        case 14:
                            $this->_setLexicalState(self::LEX_STATE_INITIAL);
                            return $this->_createToken(Parser::TK_STRING, $this->_auxBuffer);
                        case 8:
                            return $this->_createToken(Parser::TK_COMMA);
                        case 15:
                            $this->_auxBuffer .= $this->_getText();
                            break;
                        case 9:
                            return $this->_createToken(Parser::TK_COLON);
                        case 6:
                            return $this->_createToken(Parser::TK_LEFT_SQUARE);
                        case 7:
                            return $this->_createToken(Parser::TK_RIGHT_SQUARE);
                        case 3:
                        case 11:
                        case 24:
                            return $this->_createToken(Parser::TK_NUMBER);
                        case 4:
                            return $this->_createToken(Parser::TK_LEFT_BRACE);
                        case 5:
                            return $this->_createToken(Parser::TK_RIGHT_BRACE);
                        case 10:
                            break;
                        case 12:
                            return $this->_createToken(Parser::TK_NULL);
                        case 13:
                            return $this->_createToken(Parser::TK_BOOL);
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
                        case 1:
                            break;
                        default:
                            $this->_triggerError('INTERNAL');
                    }
                }

                $initial = true;
                $state = $this->_stateDtrans[$this->_lexicalState];

                $nextState = self::NO_STATE;
                $lastAcceptState = self::NO_STATE;

                //$this->_bufferStart = $this->_bufferIndex;
            } else {
                $initial = false;
                $state = $nextState;
            }
        } while (true);
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