// Generated from /home/evoup/projects/gitProjects/monitor_pass/server2/src/main/resources/Trigger.g4 by ANTLR 4.7
package com.evoupsight.monitorpass.server.exporession;
import org.antlr.v4.runtime.tree.ParseTreeVisitor;

/**
 * This interface defines a complete generic visitor for a parse tree produced
 * by {@link TriggerParser}.
 *
 * @param <T> The return type of the visit operation. Use {@link Void} for
 * operations with no return type.
 */
public interface TriggerVisitor<T> extends ParseTreeVisitor<T> {
	/**
	 * Visit a parse tree produced by the {@code Monstr}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitMonstr(TriggerParser.MonstrContext ctx);
	/**
	 * Visit a parse tree produced by the {@code Float}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitFloat(TriggerParser.FloatContext ctx);
	/**
	 * Visit a parse tree produced by the {@code BinaryExpression}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitBinaryExpression(TriggerParser.BinaryExpressionContext ctx);
	/**
	 * Visit a parse tree produced by the {@code MulDiv}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitMulDiv(TriggerParser.MulDivContext ctx);
	/**
	 * Visit a parse tree produced by the {@code AddSub}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitAddSub(TriggerParser.AddSubContext ctx);
	/**
	 * Visit a parse tree produced by the {@code Parens}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitParens(TriggerParser.ParensContext ctx);
	/**
	 * Visit a parse tree produced by the {@code ComparatorExpression}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitComparatorExpression(TriggerParser.ComparatorExpressionContext ctx);
	/**
	 * Visit a parse tree produced by the {@code NotExpression}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitNotExpression(TriggerParser.NotExpressionContext ctx);
	/**
	 * Visit a parse tree produced by the {@code Int}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitInt(TriggerParser.IntContext ctx);
	/**
	 * Visit a parse tree produced by the {@code BoolExpression}
	 * labeled alternative in {@link TriggerParser#expr}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitBoolExpression(TriggerParser.BoolExpressionContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#comparator}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitComparator(TriggerParser.ComparatorContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#bool}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitBool(TriggerParser.BoolContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#nestedCondition}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitNestedCondition(TriggerParser.NestedConditionContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#condition}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitCondition(TriggerParser.ConditionContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#component}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitComponent(TriggerParser.ComponentContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#multiAttrComp}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitMultiAttrComp(TriggerParser.MultiAttrCompContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#predicate}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitPredicate(TriggerParser.PredicateContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#unary}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitUnary(TriggerParser.UnaryContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#and}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitAnd(TriggerParser.AndContext ctx);
	/**
	 * Visit a parse tree produced by {@link TriggerParser#binary}.
	 * @param ctx the parse tree
	 * @return the visitor result
	 */
	T visitBinary(TriggerParser.BinaryContext ctx);
}