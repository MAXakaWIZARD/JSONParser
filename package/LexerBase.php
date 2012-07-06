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
class LexerBase
{
    const BUFFER_SIZE = 8192;
    const F = -1;
    const NO_STATE = -1;
    const NOT_ACCEPT = 0;
    const START = 1;
    const END = 2;
    const NO_ANCHOR = 4;
    const BOL = 65536;
    const EOF = 65537;

    const LEX_STATE_INITIAL = 0;
    const LEX_STATE_STRING_BEGIN = 1;

    protected $_reader;
    protected $_streamFilename = null;

    /**
     * @var string
     */
    protected $_buffer;

    protected $_bufferRead;
    protected $_bufferIndex;
    protected $_bufferStart;
    protected $_bufferEnd;
    protected $_char = 0;
    protected $_col = 0;
    protected $_line = 0;
    protected $_atBol;

    protected $_lexicalState;

    protected $_lastWasCr = false;
    protected $_countLines = false;
    protected $_countChars = false;

    /**
     * @var array
     */
    static $errorStrings
        = array(
            'INTERNAL' => "Error: internal error.\n",
            'MATCH'    => "Error: Unmatched input.\n"
        );

    /**
     * @param $stream
     */
    public function __construct($stream)
    {
        $this->_lexicalState = self::LEX_STATE_INITIAL;

        $this->_reader = $stream;
        $meta = stream_get_meta_data($stream);
        if (!isset($meta['uri'])) {
            $this->_streamFilename = '<<input>>';
        } else {
            $this->_streamFilename = $meta['uri'];
        }

        $this->_buffer = "";
        $this->_bufferRead = 0;
        $this->_bufferIndex = 0;
        $this->_bufferStart = 0;
        $this->_bufferEnd = 0;
        $this->_char = 0;
        $this->_line = 1;
        $this->_atBol = true;
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
            if ($data === false || !strlen($data)) {
                return self::EOF;
            }
            $this->_buffer .= $data;
            $this->_bufferRead += strlen($data);
        }

        while ($this->_bufferIndex >= $this->_bufferRead) {
            $data = fread($this->_reader, self::BUFFER_SIZE);
            if ($data === false || !strlen($data)) {
                return self::EOF;
            }
            $this->_buffer .= $data;
            $this->_bufferRead += strlen($data);
        }

        return ord($this->_buffer[$this->_bufferIndex++]);
    }

    /**
     *
     */
    protected function _moveEnd()
    {
        if ($this->_bufferEnd > $this->_bufferStart
            && $this->_buffer[$this->_bufferEnd - 1] == "\n"
        ) {
            $this->_bufferEnd--;
        }
        if ($this->_bufferEnd > $this->_bufferStart
            && $this->_buffer[$this->_bufferEnd - 1] == "\r"
        ) {
            $this->_bufferEnd--;
        }
    }

    /**
     *
     */
    protected function _markStart()
    {
        if ($this->_countLines || $this->_countChars) {
            if ($this->_countLines) {
                for ($i = $this->_bufferStart; $i < $this->_bufferIndex; ++$i) {
                    if ("\n" == $this->_buffer[$i] && !$this->_lastWasCr) {
                        ++$this->_line;
                        $this->_col = 0;
                    }
                    if ("\r" == $this->_buffer[$i]) {
                        ++$yyline;
                        $this->_col = 0;
                        $this->_lastWasCr = true;
                    } else {
                        $this->_lastWasCr = false;
                    }
                }
            }
            if ($this->_countChars) {
                $this->_char += $this->_bufferIndex - $this->_bufferStart;
                $this->_col += $this->_bufferIndex - $this->_bufferStart;
            }
            $this->_bufferStart = $this->_bufferIndex;
        }
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
        #echo "yy_to_mark: setting buffer index to ", $this->yy_buffer_end, "\n";
        $this->_bufferIndex = $this->_bufferEnd;
        $this->_atBol = ($this->_bufferEnd > $this->_bufferStart)
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
    protected function _triggerError($code, $fatal)
    {
        print self::$errorStrings[$code];
        flush();
        if ($fatal) {
            throw new \Exception("JLex fatal error " . self::$errorStrings[$code]);
        }
    }

    /**
     * creates an annotated token
     * @param null $type
     * @param null $value
     *
     * @return Token
     */
    public function createToken($type = null, $value = null)
    {
        if ($type === null) {
            $type = $this->_getText();
        }
        $tok = new Token($type);
        $this->annotateToken($tok, $value);
        return $tok;
    }

    /**
     * annotates a token with a value and source positioning
     * @param Token $tok
     * @param null      $value
     */
    public function annotateToken(Token $tok, $value = null)
    {
        $tok->value = is_null($value) ? $this->_getText() : $value;
        $tok->col = $this->_col;
        $tok->line = $this->_line;
        $tok->filename = $this->_streamFilename;
    }
}