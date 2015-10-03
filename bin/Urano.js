Urano = (function() {
  "use strict";

  /*
   * Generated by PEG.js 0.9.0.
   *
   * http://pegjs.org/
   */

  function peg$subclass(child, parent) {
    function ctor() { this.constructor = child; }
    ctor.prototype = parent.prototype;
    child.prototype = new ctor();
  }

  function peg$SyntaxError(message, expected, found, location) {
    this.message  = message;
    this.expected = expected;
    this.found    = found;
    this.location = location;
    this.name     = "SyntaxError";

    if (typeof Error.captureStackTrace === "function") {
      Error.captureStackTrace(this, peg$SyntaxError);
    }
  }

  peg$subclass(peg$SyntaxError, Error);

  function peg$parse(input) {
    var options = arguments.length > 1 ? arguments[1] : {},
        parser  = this,

        peg$FAILED = {},

        peg$startRuleIndices = { Start: 0 },
        peg$startRuleIndex   = 0,

        peg$consts = [
          function(code) {
              return code;
            },
          function(body) {
              return {
                type: "Program",
                value: Urano.list.opt(body)
              };
            },
          function(x, xs) {
              return Urano.list.build(x, xs, 1);
            },
          function(body) {
              return body !== null ? body : [];
            },
          { type: "other", description: "statement" },
          { type: "other", description: "break stmt" },
          function(level) {
              return {
                type: "BreakStmt",
                level: level !== null ? level : 1
              };
            },
          { type: "other", description: "if statement" },
          function(expr, body) {
              return {
                type: "IfStmt",
                condition: expr,
                body: body
              };
            },
          { type: "other", description: "while statement" },
          function(expr, body) {
              return {
                type: "WhileStmt",
                condition: expr,
                body: body
              };
            },
          { type: "other", description: "expression" },
          "expr",
          { type: "literal", value: "expr", description: "\"expr\"" },
          function() {
              return ["MAYBE EXPRESSION"];
            },
          function(i) {
              return {
                type: "Expr",
                value: i
              }
            },
          { type: "other", description: "reserved word" },
          { type: "other", description: "alias" },
          "alias",
          { type: "literal", value: "alias", description: "\"alias\"" },
          { type: "other", description: "and" },
          "and",
          { type: "literal", value: "and", description: "\"and\"" },
          { type: "other", description: "bool" },
          "bool",
          { type: "literal", value: "bool", description: "\"bool\"" },
          { type: "other", description: "break" },
          "break",
          { type: "literal", value: "break", description: "\"break\"" },
          { type: "other", description: "class" },
          "class",
          { type: "literal", value: "class", description: "\"class\"" },
          { type: "other", description: "declare" },
          "declare",
          { type: "literal", value: "declare", description: "\"declare\"" },
          { type: "other", description: "else" },
          "else",
          { type: "literal", value: "else", description: "\"else\"" },
          { type: "other", description: "elsif" },
          "elsif",
          { type: "literal", value: "elsif", description: "\"elsif\"" },
          { type: "other", description: "false" },
          "false",
          { type: "literal", value: "false", description: "\"false\"" },
          { type: "other", description: "finally" },
          "finally",
          { type: "literal", value: "finally", description: "\"finally\"" },
          { type: "other", description: "for" },
          "for",
          { type: "literal", value: "for", description: "\"for\"" },
          { type: "other", description: "func" },
          "func",
          { type: "literal", value: "func", description: "\"func\"" },
          { type: "other", description: "if" },
          "if",
          { type: "literal", value: "if", description: "\"if\"" },
          { type: "other", description: "import" },
          "import",
          { type: "literal", value: "import", description: "\"import\"" },
          { type: "other", description: "in" },
          "in",
          { type: "literal", value: "in", description: "\"in\"" },
          { type: "other", description: "iterate" },
          "iterate",
          { type: "literal", value: "iterate", description: "\"iterate\"" },
          { type: "other", description: "let" },
          "let",
          { type: "literal", value: "let", description: "\"let\"" },
          { type: "other", description: "loop" },
          "loop",
          { type: "literal", value: "loop", description: "\"loop\"" },
          { type: "other", description: "match" },
          "match",
          { type: "literal", value: "match", description: "\"match\"" },
          { type: "other", description: "mixed" },
          "mixed",
          { type: "literal", value: "mixed", description: "\"mixed\"" },
          { type: "other", description: "module" },
          "module",
          { type: "literal", value: "module", description: "\"module\"" },
          { type: "other", description: "nil" },
          "nil",
          { type: "literal", value: "nil", description: "\"nil\"" },
          { type: "other", description: "number" },
          "number",
          { type: "literal", value: "number", description: "\"number\"" },
          { type: "other", description: "object" },
          "object",
          { type: "literal", value: "object", description: "\"object\"" },
          { type: "other", description: "or" },
          "or",
          { type: "literal", value: "or", description: "\"or\"" },
          { type: "other", description: "otherwise" },
          "otherwise",
          { type: "literal", value: "otherwise", description: "\"otherwise\"" },
          { type: "other", description: "protocol" },
          "protocol",
          { type: "literal", value: "protocol", description: "\"protocol\"" },
          { type: "other", description: "raise" },
          "raise",
          { type: "literal", value: "raise", description: "\"raise\"" },
          { type: "other", description: "rescue" },
          "rescue",
          { type: "literal", value: "rescue", description: "\"rescue\"" },
          { type: "other", description: "resource" },
          "resource",
          { type: "literal", value: "resource", description: "\"resource\"" },
          { type: "other", description: "static" },
          "static",
          { type: "literal", value: "static", description: "\"static\"" },
          { type: "other", description: "string" },
          "string",
          { type: "literal", value: "string", description: "\"string\"" },
          { type: "other", description: "true" },
          "true",
          { type: "literal", value: "true", description: "\"true\"" },
          { type: "other", description: "try" },
          "try",
          { type: "literal", value: "try", description: "\"try\"" },
          { type: "other", description: "while" },
          "while",
          { type: "literal", value: "while", description: "\"while\"" },
          { type: "other", description: "xor" },
          "xor",
          { type: "literal", value: "xor", description: "\"xor\"" },
          { type: "other", description: "yield" },
          "yield",
          { type: "literal", value: "yield", description: "\"yield\"" },
          { type: "other", description: "plus operator" },
          "+",
          { type: "literal", value: "+", description: "\"+\"" },
          { type: "other", description: "minus operator" },
          "-",
          { type: "literal", value: "-", description: "\"-\"" },
          { type: "other", description: "decimal" },
          /^[1-9]/,
          { type: "class", value: "[1-9]", description: "[1-9]" },
          /^[0-9]/,
          { type: "class", value: "[0-9]", description: "[0-9]" },
          function(x, xs) {
              return parseInt([x].concat(xs).join(""));
            },
          "0",
          { type: "literal", value: "0", description: "\"0\"" },
          /^[xX]/,
          { type: "class", value: "[xX]", description: "[xX]" },
          function() {
              return 0;
            },
          { type: "other", description: "hexadecimal" },
          /^[0-9a-fA-F]/,
          { type: "class", value: "[0-9a-fA-F]", description: "[0-9a-fA-F]" },
          function(hexa) {
              return parseInt("0x" + hexa.join(""));
            },
          { type: "other", description: "octal" },
          /^[0-7]/,
          { type: "class", value: "[0-7]", description: "[0-7]" },
          function(octal) {
              return parseInt("0" + octal.join(""), 8);
            },
          { type: "other", description: "integer" },
          { type: "other", description: "identifier" },
          function(name) {
              return name;
            },
          function(x, xs) {
              return {
                type: "Identifier",
                value: [x].concat(xs).join("")
              };
            },
          /^[a-zA-Z_-\xFF]/,
          { type: "class", value: "[a-zA-Z_\\x7f-\\xff]", description: "[a-zA-Z_\\x7f-\\xff]" },
          /^[a-zA-Z0-9_-\xFF]/,
          { type: "class", value: "[a-zA-Z0-9_\\x7f-\\xff]", description: "[a-zA-Z0-9_\\x7f-\\xff]" },
          { type: "other", description: "left bracket" },
          "{",
          { type: "literal", value: "{", description: "\"{\"" },
          { type: "other", description: "right bracket" },
          "}",
          { type: "literal", value: "}", description: "\"}\"" },
          { type: "other", description: "left square bracket" },
          "[",
          { type: "literal", value: "[", description: "\"[\"" },
          { type: "other", description: "right square bracket" },
          "]",
          { type: "literal", value: "]", description: "\"]\"" },
          { type: "other", description: "optional whitespace" },
          /^[ \t]/,
          { type: "class", value: "[ \\t]", description: "[ \\t]" },
          { type: "other", description: "mandatory whitespace" },
          { type: "other", description: "optional newline" },
          /^[\r\n]/,
          { type: "class", value: "[\\r\\n]", description: "[\\r\\n]" },
          { type: "other", description: "mandatory newline" },
          { type: "other", description: "optional newline or whitespace" },
          { type: "other", description: "mandatory newline or whitespace" }
        ],

        peg$bytecode = [
          peg$decode("%;a/1#;!/($8\": \"! )(\"'#&'#"),
          peg$decode("%;\".\" &\"/' 8!:!!! )"),
          peg$decode("%;$/h#$%;a/,#;$/#$+\")(\"'#&'#06*%;a/,#;$/#$+\")(\"'#&'#&/2$;a/)$8#:\"#\"\"!)(#'#(\"'#&'#"),
          peg$decode("%;\".\" &\"/' 8!:#!! )"),
          peg$decode("<;%./ &;&.) &;'.# &;(=.\" 7$"),
          peg$decode("<%;-/?#;^/6$;T.\" &\"/($8#:&#! )(#'#(\"'#&'#=.\" 7%"),
          peg$decode("<%;6/q#;^/h$;(/_$;^/V$;Y/M$;a/D$;#/;$;a/2$;Z/)$8):()\"&\")()'#(('#(''#(&'#(%'#($'#(#'#(\"'#&'#=.\" 7'"),
          peg$decode("<%;L/q#;^/h$;(/_$;^/V$;Y/M$;a/D$;#/;$;a/2$;Z/)$8):*)\"&\")()'#(('#(''#(&'#(%'#($'#(#'#(\"'#&'#=.\" 7)"),
          peg$decode("<%2,\"\"6,7-/<#%<;X=.##&&!&'#/'$8\":.\" )(\"'#&'#./ &%;T/' 8!:/!! )=.\" 7+"),
          peg$decode("<;*.\xF5 &;+.\xEF &;,.\xE9 &;-.\xE3 &;..\xDD &;/.\xD7 &;0.\xD1 &;1.\xCB &;2.\xC5 &;3.\xBF &;4.\xB9 &;5.\xB3 &;6.\xAD &;7.\xA7 &;8.\xA1 &;9.\x9B &;:.\x95 &;;.\x8F &;<.\x89 &;=.\x83 &;>.} &;?.w &;@.q &;A.k &;B.e &;C._ &;D.Y &;E.S &;F.M &;G.G &;H.A &;I.; &;J.5 &;K./ &;L.) &;M.# &;N=.\" 70"),
          peg$decode("<%22\"\"6273/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 71"),
          peg$decode("<%25\"\"6576/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 74"),
          peg$decode("<%28\"\"6879/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 77"),
          peg$decode("<%2;\"\"6;7</8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7:"),
          peg$decode("<%2>\"\"6>7?/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7="),
          peg$decode("<%2A\"\"6A7B/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7@"),
          peg$decode("<%2D\"\"6D7E/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7C"),
          peg$decode("<%2G\"\"6G7H/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7F"),
          peg$decode("<%2J\"\"6J7K/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7I"),
          peg$decode("<%2M\"\"6M7N/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7L"),
          peg$decode("<%2P\"\"6P7Q/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7O"),
          peg$decode("<%2S\"\"6S7T/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7R"),
          peg$decode("<%2V\"\"6V7W/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7U"),
          peg$decode("<%2Y\"\"6Y7Z/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7X"),
          peg$decode("<%2\\\"\"6\\7]/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7["),
          peg$decode("<%2_\"\"6_7`/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7^"),
          peg$decode("<%2b\"\"6b7c/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7a"),
          peg$decode("<%2e\"\"6e7f/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7d"),
          peg$decode("<%2h\"\"6h7i/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7g"),
          peg$decode("<%2k\"\"6k7l/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7j"),
          peg$decode("<%2n\"\"6n7o/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7m"),
          peg$decode("<%2q\"\"6q7r/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7p"),
          peg$decode("<%2t\"\"6t7u/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7s"),
          peg$decode("<%2w\"\"6w7x/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7v"),
          peg$decode("<%2z\"\"6z7{/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7y"),
          peg$decode("<%2}\"\"6}7~/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7|"),
          peg$decode("<%2\x80\"\"6\x807\x81/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7"),
          peg$decode("<%2\x83\"\"6\x837\x84/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x82"),
          peg$decode("<%2\x86\"\"6\x867\x87/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x85"),
          peg$decode("<%2\x89\"\"6\x897\x8A/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x88"),
          peg$decode("<%2\x8C\"\"6\x8C7\x8D/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x8B"),
          peg$decode("<%2\x8F\"\"6\x8F7\x90/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x8E"),
          peg$decode("<%2\x92\"\"6\x927\x93/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x91"),
          peg$decode("<%2\x95\"\"6\x957\x96/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x94"),
          peg$decode("<%2\x98\"\"6\x987\x99/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x97"),
          peg$decode("<%2\x9B\"\"6\x9B7\x9C/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x9A"),
          peg$decode("<%2\x9E\"\"6\x9E7\x9F/8#%<;X=.##&&!&'#/#$+\")(\"'#&'#=.\" 7\x9D"),
          peg$decode("<2\xA1\"\"6\xA17\xA2=.\" 7\xA0"),
          peg$decode("<2\xA4\"\"6\xA47\xA5=.\" 7\xA3"),
          peg$decode("<%4\xA7\"\"5!7\xA8/E#$4\xA9\"\"5!7\xAA0)*4\xA9\"\"5!7\xAA&/)$8\":\xAB\"\"! )(\"'#&'#.R &%2\xAC\"\"6\xAC7\xAD/B#%<4\xAE\"\"5!7\xAF=.##&&!&'#/'$8\":\xB0\" )(\"'#&'#=.\" 7\xA6"),
          peg$decode("<%2\xAC\"\"6\xAC7\xAD/Y#4\xAE\"\"5!7\xAF/J$$4\xB2\"\"5!7\xB3/,#0)*4\xB2\"\"5!7\xB3&&&#/($8#:\xB4#! )(#'#(\"'#&'#=.\" 7\xB1"),
          peg$decode("<%2\xAC\"\"6\xAC7\xAD/J#$4\xB6\"\"5!7\xB7/,#0)*4\xB6\"\"5!7\xB7&&&#/($8\":\xB8\"! )(\"'#&'#=.\" 7\xB5"),
          peg$decode("<;S.) &;R.# &;Q=.\" 7\xB9"),
          peg$decode("<%%<;)=.##&&!&'#/1#;V/($8\":\xBB\"! )(\"'#&'#=.\" 7\xBA"),
          peg$decode("%;W/9#$;X0#*;X&/)$8\":\xBC\"\"! )(\"'#&'#"),
          peg$decode("4\xBD\"\"5!7\xBE"),
          peg$decode("4\xBF\"\"5!7\xC0"),
          peg$decode("<2\xC2\"\"6\xC27\xC3=.\" 7\xC1"),
          peg$decode("<2\xC5\"\"6\xC57\xC6=.\" 7\xC4"),
          peg$decode("<2\xC8\"\"6\xC87\xC9=.\" 7\xC7"),
          peg$decode("<2\xCB\"\"6\xCB7\xCC=.\" 7\xCA"),
          peg$decode("<$4\xCE\"\"5!7\xCF0)*4\xCE\"\"5!7\xCF&=.\" 7\xCD"),
          peg$decode("<$4\xCE\"\"5!7\xCF/,#0)*4\xCE\"\"5!7\xCF&&&#=.\" 7\xD0"),
          peg$decode("<$4\xD2\"\"5!7\xD30)*4\xD2\"\"5!7\xD3&=.\" 7\xD1"),
          peg$decode("<$4\xD2\"\"5!7\xD3/,#0)*4\xD2\"\"5!7\xD3&&&#=.\" 7\xD4"),
          peg$decode("<$4\xCE\"\"5!7\xCF.) &4\xD2\"\"5!7\xD305*4\xCE\"\"5!7\xCF.) &4\xD2\"\"5!7\xD3&=.\" 7\xD5"),
          peg$decode("<;^.# &;`=.\" 7\xD6")
        ],

        peg$currPos          = 0,
        peg$savedPos         = 0,
        peg$posDetailsCache  = [{ line: 1, column: 1, seenCR: false }],
        peg$maxFailPos       = 0,
        peg$maxFailExpected  = [],
        peg$silentFails      = 0,

        peg$result;

    if ("startRule" in options) {
      if (!(options.startRule in peg$startRuleIndices)) {
        throw new Error("Can't start parsing from rule \"" + options.startRule + "\".");
      }

      peg$startRuleIndex = peg$startRuleIndices[options.startRule];
    }

    function text() {
      return input.substring(peg$savedPos, peg$currPos);
    }

    function location() {
      return peg$computeLocation(peg$savedPos, peg$currPos);
    }

    function expected(description) {
      throw peg$buildException(
        null,
        [{ type: "other", description: description }],
        input.substring(peg$savedPos, peg$currPos),
        peg$computeLocation(peg$savedPos, peg$currPos)
      );
    }

    function error(message) {
      throw peg$buildException(
        message,
        null,
        input.substring(peg$savedPos, peg$currPos),
        peg$computeLocation(peg$savedPos, peg$currPos)
      );
    }

    function peg$computePosDetails(pos) {
      var details = peg$posDetailsCache[pos],
          p, ch;

      if (details) {
        return details;
      } else {
        p = pos - 1;
        while (!peg$posDetailsCache[p]) {
          p--;
        }

        details = peg$posDetailsCache[p];
        details = {
          line:   details.line,
          column: details.column,
          seenCR: details.seenCR
        };

        while (p < pos) {
          ch = input.charAt(p);
          if (ch === "\n") {
            if (!details.seenCR) { details.line++; }
            details.column = 1;
            details.seenCR = false;
          } else if (ch === "\r" || ch === "\u2028" || ch === "\u2029") {
            details.line++;
            details.column = 1;
            details.seenCR = true;
          } else {
            details.column++;
            details.seenCR = false;
          }

          p++;
        }

        peg$posDetailsCache[pos] = details;
        return details;
      }
    }

    function peg$computeLocation(startPos, endPos) {
      var startPosDetails = peg$computePosDetails(startPos),
          endPosDetails   = peg$computePosDetails(endPos);

      return {
        start: {
          offset: startPos,
          line:   startPosDetails.line,
          column: startPosDetails.column
        },
        end: {
          offset: endPos,
          line:   endPosDetails.line,
          column: endPosDetails.column
        }
      };
    }

    function peg$fail(expected) {
      if (peg$currPos < peg$maxFailPos) { return; }

      if (peg$currPos > peg$maxFailPos) {
        peg$maxFailPos = peg$currPos;
        peg$maxFailExpected = [];
      }

      peg$maxFailExpected.push(expected);
    }

    function peg$buildException(message, expected, found, location) {
      function cleanupExpected(expected) {
        var i = 1;

        expected.sort(function(a, b) {
          if (a.description < b.description) {
            return -1;
          } else if (a.description > b.description) {
            return 1;
          } else {
            return 0;
          }
        });

        while (i < expected.length) {
          if (expected[i - 1] === expected[i]) {
            expected.splice(i, 1);
          } else {
            i++;
          }
        }
      }

      function buildMessage(expected, found) {
        function stringEscape(s) {
          function hex(ch) { return ch.charCodeAt(0).toString(16).toUpperCase(); }

          return s
            .replace(/\\/g,   '\\\\')
            .replace(/"/g,    '\\"')
            .replace(/\x08/g, '\\b')
            .replace(/\t/g,   '\\t')
            .replace(/\n/g,   '\\n')
            .replace(/\f/g,   '\\f')
            .replace(/\r/g,   '\\r')
            .replace(/[\x00-\x07\x0B\x0E\x0F]/g, function(ch) { return '\\x0' + hex(ch); })
            .replace(/[\x10-\x1F\x80-\xFF]/g,    function(ch) { return '\\x'  + hex(ch); })
            .replace(/[\u0100-\u0FFF]/g,         function(ch) { return '\\u0' + hex(ch); })
            .replace(/[\u1000-\uFFFF]/g,         function(ch) { return '\\u'  + hex(ch); });
        }

        var expectedDescs = new Array(expected.length),
            expectedDesc, foundDesc, i;

        for (i = 0; i < expected.length; i++) {
          expectedDescs[i] = expected[i].description;
        }

        expectedDesc = expected.length > 1
          ? expectedDescs.slice(0, -1).join(", ")
              + " or "
              + expectedDescs[expected.length - 1]
          : expectedDescs[0];

        foundDesc = found ? "\"" + stringEscape(found) + "\"" : "end of input";

        return "Expected " + expectedDesc + " but " + foundDesc + " found.";
      }

      if (expected !== null) {
        cleanupExpected(expected);
      }

      return new peg$SyntaxError(
        message !== null ? message : buildMessage(expected, found),
        expected,
        found,
        location
      );
    }

    function peg$decode(s) {
      var bc = new Array(s.length), i;

      for (i = 0; i < s.length; i++) {
        bc[i] = s.charCodeAt(i) - 32;
      }

      return bc;
    }

    function peg$parseRule(index) {
      var bc    = peg$bytecode[index],
          ip    = 0,
          ips   = [],
          end   = bc.length,
          ends  = [],
          stack = [],
          params, i;

      while (true) {
        while (ip < end) {
          switch (bc[ip]) {
            case 0:
              stack.push(peg$consts[bc[ip + 1]]);
              ip += 2;
              break;

            case 1:
              stack.push(void 0);
              ip++;
              break;

            case 2:
              stack.push(null);
              ip++;
              break;

            case 3:
              stack.push(peg$FAILED);
              ip++;
              break;

            case 4:
              stack.push([]);
              ip++;
              break;

            case 5:
              stack.push(peg$currPos);
              ip++;
              break;

            case 6:
              stack.pop();
              ip++;
              break;

            case 7:
              peg$currPos = stack.pop();
              ip++;
              break;

            case 8:
              stack.length -= bc[ip + 1];
              ip += 2;
              break;

            case 9:
              stack.splice(-2, 1);
              ip++;
              break;

            case 10:
              stack[stack.length - 2].push(stack.pop());
              ip++;
              break;

            case 11:
              stack.push(stack.splice(stack.length - bc[ip + 1], bc[ip + 1]));
              ip += 2;
              break;

            case 12:
              stack.push(input.substring(stack.pop(), peg$currPos));
              ip++;
              break;

            case 13:
              ends.push(end);
              ips.push(ip + 3 + bc[ip + 1] + bc[ip + 2]);

              if (stack[stack.length - 1]) {
                end = ip + 3 + bc[ip + 1];
                ip += 3;
              } else {
                end = ip + 3 + bc[ip + 1] + bc[ip + 2];
                ip += 3 + bc[ip + 1];
              }

              break;

            case 14:
              ends.push(end);
              ips.push(ip + 3 + bc[ip + 1] + bc[ip + 2]);

              if (stack[stack.length - 1] === peg$FAILED) {
                end = ip + 3 + bc[ip + 1];
                ip += 3;
              } else {
                end = ip + 3 + bc[ip + 1] + bc[ip + 2];
                ip += 3 + bc[ip + 1];
              }

              break;

            case 15:
              ends.push(end);
              ips.push(ip + 3 + bc[ip + 1] + bc[ip + 2]);

              if (stack[stack.length - 1] !== peg$FAILED) {
                end = ip + 3 + bc[ip + 1];
                ip += 3;
              } else {
                end = ip + 3 + bc[ip + 1] + bc[ip + 2];
                ip += 3 + bc[ip + 1];
              }

              break;

            case 16:
              if (stack[stack.length - 1] !== peg$FAILED) {
                ends.push(end);
                ips.push(ip);

                end = ip + 2 + bc[ip + 1];
                ip += 2;
              } else {
                ip += 2 + bc[ip + 1];
              }

              break;

            case 17:
              ends.push(end);
              ips.push(ip + 3 + bc[ip + 1] + bc[ip + 2]);

              if (input.length > peg$currPos) {
                end = ip + 3 + bc[ip + 1];
                ip += 3;
              } else {
                end = ip + 3 + bc[ip + 1] + bc[ip + 2];
                ip += 3 + bc[ip + 1];
              }

              break;

            case 18:
              ends.push(end);
              ips.push(ip + 4 + bc[ip + 2] + bc[ip + 3]);

              if (input.substr(peg$currPos, peg$consts[bc[ip + 1]].length) === peg$consts[bc[ip + 1]]) {
                end = ip + 4 + bc[ip + 2];
                ip += 4;
              } else {
                end = ip + 4 + bc[ip + 2] + bc[ip + 3];
                ip += 4 + bc[ip + 2];
              }

              break;

            case 19:
              ends.push(end);
              ips.push(ip + 4 + bc[ip + 2] + bc[ip + 3]);

              if (input.substr(peg$currPos, peg$consts[bc[ip + 1]].length).toLowerCase() === peg$consts[bc[ip + 1]]) {
                end = ip + 4 + bc[ip + 2];
                ip += 4;
              } else {
                end = ip + 4 + bc[ip + 2] + bc[ip + 3];
                ip += 4 + bc[ip + 2];
              }

              break;

            case 20:
              ends.push(end);
              ips.push(ip + 4 + bc[ip + 2] + bc[ip + 3]);

              if (peg$consts[bc[ip + 1]].test(input.charAt(peg$currPos))) {
                end = ip + 4 + bc[ip + 2];
                ip += 4;
              } else {
                end = ip + 4 + bc[ip + 2] + bc[ip + 3];
                ip += 4 + bc[ip + 2];
              }

              break;

            case 21:
              stack.push(input.substr(peg$currPos, bc[ip + 1]));
              peg$currPos += bc[ip + 1];
              ip += 2;
              break;

            case 22:
              stack.push(peg$consts[bc[ip + 1]]);
              peg$currPos += peg$consts[bc[ip + 1]].length;
              ip += 2;
              break;

            case 23:
              stack.push(peg$FAILED);
              if (peg$silentFails === 0) {
                peg$fail(peg$consts[bc[ip + 1]]);
              }
              ip += 2;
              break;

            case 24:
              peg$savedPos = stack[stack.length - 1 - bc[ip + 1]];
              ip += 2;
              break;

            case 25:
              peg$savedPos = peg$currPos;
              ip++;
              break;

            case 26:
              params = bc.slice(ip + 4, ip + 4 + bc[ip + 3]);
              for (i = 0; i < bc[ip + 3]; i++) {
                params[i] = stack[stack.length - 1 - params[i]];
              }

              stack.splice(
                stack.length - bc[ip + 2],
                bc[ip + 2],
                peg$consts[bc[ip + 1]].apply(null, params)
              );

              ip += 4 + bc[ip + 3];
              break;

            case 27:
              stack.push(peg$parseRule(bc[ip + 1]));
              ip += 2;
              break;

            case 28:
              peg$silentFails++;
              ip++;
              break;

            case 29:
              peg$silentFails--;
              ip++;
              break;

            default:
              throw new Error("Invalid opcode: " + bc[ip] + ".");
          }
        }

        if (ends.length > 0) {
          end = ends.pop();
          ip = ips.pop();
        } else {
          break;
        }
      }

      return stack[0];
    }


      var Urano = {
        list: {
          build: function(x, xs, n) {
            return [x].concat(Urano.list.take(xs, n));
          },
          take: function(xs, n) {
            var result = new Array(xs.length);

            for (var i = 0, len = xs.length; i < len; i++) {
              result[i] = xs[i][n];
            }

            return result;
          },
          opt: function(value) {
            return value !== null ? value : [];
          }
        }
      };


    peg$result = peg$parseRule(peg$startRuleIndex);

    if (peg$result !== peg$FAILED && peg$currPos === input.length) {
      return peg$result;
    } else {
      if (peg$result !== peg$FAILED && peg$currPos < input.length) {
        peg$fail({ type: "end", description: "end of input" });
      }

      throw peg$buildException(
        null,
        peg$maxFailExpected,
        peg$maxFailPos < input.length ? input.charAt(peg$maxFailPos) : null,
        peg$maxFailPos < input.length
          ? peg$computeLocation(peg$maxFailPos, peg$maxFailPos + 1)
          : peg$computeLocation(peg$maxFailPos, peg$maxFailPos)
      );
    }
  }

  return {
    SyntaxError: peg$SyntaxError,
    parse:       peg$parse
  };
})();
