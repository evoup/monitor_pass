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
		INT=1, MUL=2, DIV=3, ADD=4, SUB=5, GT=6, GE=7, LT=8, LE=9, EQ=10, WS=11, 
		LPAREN=12, RPAREN=13, TRUE=14, FALSE=15, MONSTR=16, COMMENT=17, AND=18, 
		OR=19;
	public static String[] channelNames = {
		"DEFAULT_TOKEN_CHANNEL", "HIDDEN"
	};

	public static String[] modeNames = {
		"DEFAULT_MODE"
	};

	public static final String[] ruleNames = {
		"INT", "MUL", "DIV", "ADD", "SUB", "GT", "GE", "LT", "LE", "EQ", "WS", 
		"LPAREN", "RPAREN", "TRUE", "FALSE", "MONSTR", "COMMENT", "AND", "OR"
	};

	private static final String[] _LITERAL_NAMES = {
		null, null, "'*'", "'/'", "'+'", "'-'", "'>'", "'>='", "'<'", "'<='", 
		"'='", null, "'('", "')'", "'TRUE'", "'FALSE'", null, null, "'AND'", "'OR'"
	};
	private static final String[] _SYMBOLIC_NAMES = {
		null, "INT", "MUL", "DIV", "ADD", "SUB", "GT", "GE", "LT", "LE", "EQ", 
		"WS", "LPAREN", "RPAREN", "TRUE", "FALSE", "MONSTR", "COMMENT", "AND", 
		"OR"
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
		"\3\u608b\ua72a\u8133\ub9ed\u417c\u3be7\u7786\u5964\2\25v\b\1\4\2\t\2\4"+
		"\3\t\3\4\4\t\4\4\5\t\5\4\6\t\6\4\7\t\7\4\b\t\b\4\t\t\t\4\n\t\n\4\13\t"+
		"\13\4\f\t\f\4\r\t\r\4\16\t\16\4\17\t\17\4\20\t\20\4\21\t\21\4\22\t\22"+
		"\4\23\t\23\4\24\t\24\3\2\6\2+\n\2\r\2\16\2,\3\3\3\3\3\4\3\4\3\5\3\5\3"+
		"\6\3\6\3\7\3\7\3\b\3\b\3\b\3\t\3\t\3\n\3\n\3\n\3\13\3\13\3\f\6\fD\n\f"+
		"\r\f\16\fE\3\f\3\f\3\r\3\r\3\16\3\16\3\17\3\17\3\17\3\17\3\17\3\20\3\20"+
		"\3\20\3\20\3\20\3\20\3\21\3\21\7\21[\n\21\f\21\16\21^\13\21\3\21\3\21"+
		"\3\22\3\22\3\22\3\22\7\22f\n\22\f\22\16\22i\13\22\3\22\3\22\3\22\3\22"+
		"\3\22\3\23\3\23\3\23\3\23\3\24\3\24\3\24\4\\g\2\25\3\3\5\4\7\5\t\6\13"+
		"\7\r\b\17\t\21\n\23\13\25\f\27\r\31\16\33\17\35\20\37\21!\22#\23%\24\'"+
		"\25\3\2\4\3\2\62;\5\2\13\f\17\17\"\"\2y\2\3\3\2\2\2\2\5\3\2\2\2\2\7\3"+
		"\2\2\2\2\t\3\2\2\2\2\13\3\2\2\2\2\r\3\2\2\2\2\17\3\2\2\2\2\21\3\2\2\2"+
		"\2\23\3\2\2\2\2\25\3\2\2\2\2\27\3\2\2\2\2\31\3\2\2\2\2\33\3\2\2\2\2\35"+
		"\3\2\2\2\2\37\3\2\2\2\2!\3\2\2\2\2#\3\2\2\2\2%\3\2\2\2\2\'\3\2\2\2\3*"+
		"\3\2\2\2\5.\3\2\2\2\7\60\3\2\2\2\t\62\3\2\2\2\13\64\3\2\2\2\r\66\3\2\2"+
		"\2\178\3\2\2\2\21;\3\2\2\2\23=\3\2\2\2\25@\3\2\2\2\27C\3\2\2\2\31I\3\2"+
		"\2\2\33K\3\2\2\2\35M\3\2\2\2\37R\3\2\2\2!X\3\2\2\2#a\3\2\2\2%o\3\2\2\2"+
		"\'s\3\2\2\2)+\t\2\2\2*)\3\2\2\2+,\3\2\2\2,*\3\2\2\2,-\3\2\2\2-\4\3\2\2"+
		"\2./\7,\2\2/\6\3\2\2\2\60\61\7\61\2\2\61\b\3\2\2\2\62\63\7-\2\2\63\n\3"+
		"\2\2\2\64\65\7/\2\2\65\f\3\2\2\2\66\67\7@\2\2\67\16\3\2\2\289\7@\2\29"+
		":\7?\2\2:\20\3\2\2\2;<\7>\2\2<\22\3\2\2\2=>\7>\2\2>?\7?\2\2?\24\3\2\2"+
		"\2@A\7?\2\2A\26\3\2\2\2BD\t\3\2\2CB\3\2\2\2DE\3\2\2\2EC\3\2\2\2EF\3\2"+
		"\2\2FG\3\2\2\2GH\b\f\2\2H\30\3\2\2\2IJ\7*\2\2J\32\3\2\2\2KL\7+\2\2L\34"+
		"\3\2\2\2MN\7V\2\2NO\7T\2\2OP\7W\2\2PQ\7G\2\2Q\36\3\2\2\2RS\7H\2\2ST\7"+
		"C\2\2TU\7N\2\2UV\7U\2\2VW\7G\2\2W \3\2\2\2X\\\7}\2\2Y[\13\2\2\2ZY\3\2"+
		"\2\2[^\3\2\2\2\\]\3\2\2\2\\Z\3\2\2\2]_\3\2\2\2^\\\3\2\2\2_`\7\177\2\2"+
		"`\"\3\2\2\2ab\7\61\2\2bc\7,\2\2cg\3\2\2\2df\13\2\2\2ed\3\2\2\2fi\3\2\2"+
		"\2gh\3\2\2\2ge\3\2\2\2hj\3\2\2\2ig\3\2\2\2jk\7,\2\2kl\7\61\2\2lm\3\2\2"+
		"\2mn\b\22\2\2n$\3\2\2\2op\7C\2\2pq\7P\2\2qr\7F\2\2r&\3\2\2\2st\7Q\2\2"+
		"tu\7T\2\2u(\3\2\2\2\7\2,E\\g\3\b\2\2";
	public static final ATN _ATN =
		new ATNDeserializer().deserialize(_serializedATN.toCharArray());
	static {
		_decisionToDFA = new DFA[_ATN.getNumberOfDecisions()];
		for (int i = 0; i < _ATN.getNumberOfDecisions(); i++) {
			_decisionToDFA[i] = new DFA(_ATN.getDecisionState(i), i);
		}
	}
}