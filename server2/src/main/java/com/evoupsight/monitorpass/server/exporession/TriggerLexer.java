// Generated from /home/evoup/projects/gitProjects/monitor_pass/server2/src/main/resources/Trigger.g4 by ANTLR 4.7
package com.evoupsight.monitorpass.server.exporession;
import org.antlr.v4.runtime.Lexer;
import org.antlr.v4.runtime.CharStream;
import org.antlr.v4.runtime.Token;
import org.antlr.v4.runtime.TokenStream;
import org.antlr.v4.runtime.*;
import org.antlr.v4.runtime.atn.*;
import org.antlr.v4.runtime.dfa.DFA;
import org.antlr.v4.runtime.misc.*;

@SuppressWarnings({"all", "warnings", "unchecked", "unused", "cast"})
public class TriggerLexer extends Lexer {
	static { RuntimeMetaData.checkVersion("4.7", RuntimeMetaData.VERSION); }

	protected static final DFA[] _decisionToDFA;
	protected static final PredictionContextCache _sharedContextCache =
		new PredictionContextCache();
	public static final int
		INT=1, FLT=2, MUL=3, DIV=4, ADD=5, SUB=6, GT=7, GE=8, LT=9, LE=10, EQ=11, 
		WS=12, LPAREN=13, RPAREN=14, TRUE=15, FALSE=16, MONSTR=17, COMMENT=18, 
		AND=19, OR=20;
	public static String[] channelNames = {
		"DEFAULT_TOKEN_CHANNEL", "HIDDEN"
	};

	public static String[] modeNames = {
		"DEFAULT_MODE"
	};

	public static final String[] ruleNames = {
		"INT", "FLT", "MUL", "DIV", "ADD", "SUB", "GT", "GE", "LT", "LE", "EQ", 
		"WS", "LPAREN", "RPAREN", "TRUE", "FALSE", "MONSTR", "COMMENT", "AND", 
		"OR"
	};

	private static final String[] _LITERAL_NAMES = {
		null, null, null, "'*'", "'/'", "'+'", "'-'", "'>'", "'>='", "'<'", "'<='", 
		"'='", null, "'('", "')'", "'TRUE'", "'FALSE'", null, null, "'AND'", "'OR'"
	};
	private static final String[] _SYMBOLIC_NAMES = {
		null, "INT", "FLT", "MUL", "DIV", "ADD", "SUB", "GT", "GE", "LT", "LE", 
		"EQ", "WS", "LPAREN", "RPAREN", "TRUE", "FALSE", "MONSTR", "COMMENT", 
		"AND", "OR"
	};
	public static final Vocabulary VOCABULARY = new VocabularyImpl(_LITERAL_NAMES, _SYMBOLIC_NAMES);

	/**
	 * @deprecated Use {@link #VOCABULARY} instead.
	 */
	@Deprecated
	public static final String[] tokenNames;
	static {
		tokenNames = new String[_SYMBOLIC_NAMES.length];
		for (int i = 0; i < tokenNames.length; i++) {
			tokenNames[i] = VOCABULARY.getLiteralName(i);
			if (tokenNames[i] == null) {
				tokenNames[i] = VOCABULARY.getSymbolicName(i);
			}

			if (tokenNames[i] == null) {
				tokenNames[i] = "<INVALID>";
			}
		}
	}

	@Override
	@Deprecated
	public String[] getTokenNames() {
		return tokenNames;
	}

	@Override

	public Vocabulary getVocabulary() {
		return VOCABULARY;
	}


	public TriggerLexer(CharStream input) {
		super(input);
		_interp = new LexerATNSimulator(this,_ATN,_decisionToDFA,_sharedContextCache);
	}

	@Override
	public String getGrammarFileName() { return "Trigger.g4"; }

	@Override
	public String[] getRuleNames() { return ruleNames; }

	@Override
	public String getSerializedATN() { return _serializedATN; }

	@Override
	public String[] getChannelNames() { return channelNames; }

	@Override
	public String[] getModeNames() { return modeNames; }

	@Override
	public ATN getATN() { return _ATN; }

	public static final String _serializedATN =
		"\3\u608b\ua72a\u8133\ub9ed\u417c\u3be7\u7786\u5964\2\26\u008c\b\1\4\2"+
		"\t\2\4\3\t\3\4\4\t\4\4\5\t\5\4\6\t\6\4\7\t\7\4\b\t\b\4\t\t\t\4\n\t\n\4"+
		"\13\t\13\4\f\t\f\4\r\t\r\4\16\t\16\4\17\t\17\4\20\t\20\4\21\t\21\4\22"+
		"\t\22\4\23\t\23\4\24\t\24\4\25\t\25\3\2\6\2-\n\2\r\2\16\2.\3\3\6\3\62"+
		"\n\3\r\3\16\3\63\3\3\3\3\7\38\n\3\f\3\16\3;\13\3\3\3\3\3\6\3?\n\3\r\3"+
		"\16\3@\5\3C\n\3\3\4\3\4\3\5\3\5\3\6\3\6\3\7\3\7\3\b\3\b\3\t\3\t\3\t\3"+
		"\n\3\n\3\13\3\13\3\13\3\f\3\f\3\r\6\rZ\n\r\r\r\16\r[\3\r\3\r\3\16\3\16"+
		"\3\17\3\17\3\20\3\20\3\20\3\20\3\20\3\21\3\21\3\21\3\21\3\21\3\21\3\22"+
		"\3\22\7\22q\n\22\f\22\16\22t\13\22\3\22\3\22\3\23\3\23\3\23\3\23\7\23"+
		"|\n\23\f\23\16\23\177\13\23\3\23\3\23\3\23\3\23\3\23\3\24\3\24\3\24\3"+
		"\24\3\25\3\25\3\25\4r}\2\26\3\3\5\4\7\5\t\6\13\7\r\b\17\t\21\n\23\13\25"+
		"\f\27\r\31\16\33\17\35\20\37\21!\22#\23%\24\'\25)\26\3\2\4\3\2\62;\5\2"+
		"\13\f\17\17\"\"\2\u0093\2\3\3\2\2\2\2\5\3\2\2\2\2\7\3\2\2\2\2\t\3\2\2"+
		"\2\2\13\3\2\2\2\2\r\3\2\2\2\2\17\3\2\2\2\2\21\3\2\2\2\2\23\3\2\2\2\2\25"+
		"\3\2\2\2\2\27\3\2\2\2\2\31\3\2\2\2\2\33\3\2\2\2\2\35\3\2\2\2\2\37\3\2"+
		"\2\2\2!\3\2\2\2\2#\3\2\2\2\2%\3\2\2\2\2\'\3\2\2\2\2)\3\2\2\2\3,\3\2\2"+
		"\2\5B\3\2\2\2\7D\3\2\2\2\tF\3\2\2\2\13H\3\2\2\2\rJ\3\2\2\2\17L\3\2\2\2"+
		"\21N\3\2\2\2\23Q\3\2\2\2\25S\3\2\2\2\27V\3\2\2\2\31Y\3\2\2\2\33_\3\2\2"+
		"\2\35a\3\2\2\2\37c\3\2\2\2!h\3\2\2\2#n\3\2\2\2%w\3\2\2\2\'\u0085\3\2\2"+
		"\2)\u0089\3\2\2\2+-\t\2\2\2,+\3\2\2\2-.\3\2\2\2.,\3\2\2\2./\3\2\2\2/\4"+
		"\3\2\2\2\60\62\5\3\2\2\61\60\3\2\2\2\62\63\3\2\2\2\63\61\3\2\2\2\63\64"+
		"\3\2\2\2\64\65\3\2\2\2\659\7\60\2\2\668\5\3\2\2\67\66\3\2\2\28;\3\2\2"+
		"\29\67\3\2\2\29:\3\2\2\2:C\3\2\2\2;9\3\2\2\2<>\7\60\2\2=?\5\3\2\2>=\3"+
		"\2\2\2?@\3\2\2\2@>\3\2\2\2@A\3\2\2\2AC\3\2\2\2B\61\3\2\2\2B<\3\2\2\2C"+
		"\6\3\2\2\2DE\7,\2\2E\b\3\2\2\2FG\7\61\2\2G\n\3\2\2\2HI\7-\2\2I\f\3\2\2"+
		"\2JK\7/\2\2K\16\3\2\2\2LM\7@\2\2M\20\3\2\2\2NO\7@\2\2OP\7?\2\2P\22\3\2"+
		"\2\2QR\7>\2\2R\24\3\2\2\2ST\7>\2\2TU\7?\2\2U\26\3\2\2\2VW\7?\2\2W\30\3"+
		"\2\2\2XZ\t\3\2\2YX\3\2\2\2Z[\3\2\2\2[Y\3\2\2\2[\\\3\2\2\2\\]\3\2\2\2]"+
		"^\b\r\2\2^\32\3\2\2\2_`\7*\2\2`\34\3\2\2\2ab\7+\2\2b\36\3\2\2\2cd\7V\2"+
		"\2de\7T\2\2ef\7W\2\2fg\7G\2\2g \3\2\2\2hi\7H\2\2ij\7C\2\2jk\7N\2\2kl\7"+
		"U\2\2lm\7G\2\2m\"\3\2\2\2nr\7}\2\2oq\13\2\2\2po\3\2\2\2qt\3\2\2\2rs\3"+
		"\2\2\2rp\3\2\2\2su\3\2\2\2tr\3\2\2\2uv\7\177\2\2v$\3\2\2\2wx\7\61\2\2"+
		"xy\7,\2\2y}\3\2\2\2z|\13\2\2\2{z\3\2\2\2|\177\3\2\2\2}~\3\2\2\2}{\3\2"+
		"\2\2~\u0080\3\2\2\2\177}\3\2\2\2\u0080\u0081\7,\2\2\u0081\u0082\7\61\2"+
		"\2\u0082\u0083\3\2\2\2\u0083\u0084\b\23\2\2\u0084&\3\2\2\2\u0085\u0086"+
		"\7C\2\2\u0086\u0087\7P\2\2\u0087\u0088\7F\2\2\u0088(\3\2\2\2\u0089\u008a"+
		"\7Q\2\2\u008a\u008b\7T\2\2\u008b*\3\2\2\2\13\2.\639@B[r}\3\b\2\2";
	public static final ATN _ATN =
		new ATNDeserializer().deserialize(_serializedATN.toCharArray());
	static {
		_decisionToDFA = new DFA[_ATN.getNumberOfDecisions()];
		for (int i = 0; i < _ATN.getNumberOfDecisions(); i++) {
			_decisionToDFA[i] = new DFA(_ATN.getDecisionState(i), i);
		}
	}
}