// Generated from /home/evoup/projects/gitProjects/monitor_pass/server2/src/main/resources/Server.g4 by ANTLR 4.7
package com.evoupsight.monitorpass.server.exporession;
import org.antlr.v4.runtime.tree.ParseTreeListener;

/**
 * This interface defines a complete listener for a parse tree produced by
 * {@link ServerParser}.
 */
public interface ServerListener extends ParseTreeListener {
	/**
	 * Enter a parse tree produced by the {@code Monstr}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterMonstr(ServerParser.MonstrContext ctx);
	/**
	 * Exit a parse tree produced by the {@code Monstr}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitMonstr(ServerParser.MonstrContext ctx);
	/**
	 * Enter a parse tree produced by the {@code BinaryExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterBinaryExpression(ServerParser.BinaryExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code BinaryExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitBinaryExpression(ServerParser.BinaryExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code MulDiv}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterMulDiv(ServerParser.MulDivContext ctx);
	/**
	 * Exit a parse tree produced by the {@code MulDiv}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitMulDiv(ServerParser.MulDivContext ctx);
	/**
	 * Enter a parse tree produced by the {@code AddSub}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterAddSub(ServerParser.AddSubContext ctx);
	/**
	 * Exit a parse tree produced by the {@code AddSub}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitAddSub(ServerParser.AddSubContext ctx);
	/**
	 * Enter a parse tree produced by the {@code Parens}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterParens(ServerParser.ParensContext ctx);
	/**
	 * Exit a parse tree produced by the {@code Parens}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitParens(ServerParser.ParensContext ctx);
	/**
	 * Enter a parse tree produced by the {@code ComparatorExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterComparatorExpression(ServerParser.ComparatorExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code ComparatorExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitComparatorExpression(ServerParser.ComparatorExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code NotExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterNotExpression(ServerParser.NotExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code NotExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitNotExpression(ServerParser.NotExpressionContext ctx);
	/**
	 * Enter a parse tree produced by the {@code Int}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterInt(ServerParser.IntContext ctx);
	/**
	 * Exit a parse tree produced by the {@code Int}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitInt(ServerParser.IntContext ctx);
	/**
	 * Enter a parse tree produced by the {@code BoolExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void enterBoolExpression(ServerParser.BoolExpressionContext ctx);
	/**
	 * Exit a parse tree produced by the {@code BoolExpression}
	 * labeled alternative in {@link ServerParser#expr}.
	 * @param ctx the parse tree
	 */
	void exitBoolExpression(ServerParser.BoolExpressionContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#comparator}.
	 * @param ctx the parse tree
	 */
	void enterComparator(ServerParser.ComparatorContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#comparator}.
	 * @param ctx the parse tree
	 */
	void exitComparator(ServerParser.ComparatorContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#bool}.
	 * @param ctx the parse tree
	 */
	void enterBool(ServerParser.BoolContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#bool}.
	 * @param ctx the parse tree
	 */
	void exitBool(ServerParser.BoolContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#nestedCondition}.
	 * @param ctx the parse tree
	 */
	void enterNestedCondition(ServerParser.NestedConditionContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#nestedCondition}.
	 * @param ctx the parse tree
	 */
	void exitNestedCondition(ServerParser.NestedConditionContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#condition}.
	 * @param ctx the parse tree
	 */
	void enterCondition(ServerParser.ConditionContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#condition}.
	 * @param ctx the parse tree
	 */
	void exitCondition(ServerParser.ConditionContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#component}.
	 * @param ctx the parse tree
	 */
	void enterComponent(ServerParser.ComponentContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#component}.
	 * @param ctx the parse tree
	 */
	void exitComponent(ServerParser.ComponentContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#multiAttrComp}.
	 * @param ctx the parse tree
	 */
	void enterMultiAttrComp(ServerParser.MultiAttrCompContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#multiAttrComp}.
	 * @param ctx the parse tree
	 */
	void exitMultiAttrComp(ServerParser.MultiAttrCompContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#predicate}.
	 * @param ctx the parse tree
	 */
	void enterPredicate(ServerParser.PredicateContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#predicate}.
	 * @param ctx the parse tree
	 */
	void exitPredicate(ServerParser.PredicateContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#unary}.
	 * @param ctx the parse tree
	 */
	void enterUnary(ServerParser.UnaryContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#unary}.
	 * @param ctx the parse tree
	 */
	void exitUnary(ServerParser.UnaryContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#and}.
	 * @param ctx the parse tree
	 */
	void enterAnd(ServerParser.AndContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#and}.
	 * @param ctx the parse tree
	 */
	void exitAnd(ServerParser.AndContext ctx);
	/**
	 * Enter a parse tree produced by {@link ServerParser#binary}.
	 * @param ctx the parse tree
	 */
	void enterBinary(ServerParser.BinaryContext ctx);
	/**
	 * Exit a parse tree produced by {@link ServerParser#binary}.
	 * @param ctx the parse tree
	 */
	void exitBinary(ServerParser.BinaryContext ctx);
}