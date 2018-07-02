package com.evoupsight.monitorpass.server.exporession;

import org.apache.commons.lang.math.NumberUtils;

public class MainVisitor {
    private static class Visitor extends TriggerBaseVisitor<Object> {
        @Override
        public Object visitParens(TriggerParser.ParensContext ctx) {
            return visit(ctx.expr());
        }

        @Override
        public Object visitMulDiv(TriggerParser.MulDivContext ctx) {
            Integer left = (Integer) visit(ctx.expr(0));
            Integer right = (Integer) visit(ctx.expr(1));
            if (ctx.op.getType() == TriggerParser.MUL) {
                return left * right;
            } else {
                return left / right;
            }
        }

        @Override
        public Object visitAddSub(TriggerParser.AddSubContext ctx) {
            int left = (Integer) visit(ctx.expr(0));
            int right = (Integer) visit(ctx.expr(1));
            if (ctx.op.getType() == TriggerParser.ADD) {
                return left + right;
            } else {
                return left - right;
            }
        }

        @Override
        public Object visitComparatorExpression(TriggerParser.ComparatorExpressionContext ctx) {
            if (ctx.op.EQ() != null) {
                return this.visit(ctx.left).equals(this.visit(ctx.right));
            }
            else if (ctx.op.LE() != null) {
                return asDouble(visit(ctx.left)) <= asDouble(visit(ctx.right));
            }
            else if (ctx.op.GE() != null) {
                return asDouble(visit(ctx.left)) >= asDouble(visit(ctx.right));
            }
            else if (ctx.op.LT() != null) {
                return asDouble(visit(ctx.left)) < asDouble(visit(ctx.right));
            }
            else if (ctx.op.GT() != null) {
                return asDouble(visit(ctx.left)) > asDouble(visit(ctx.right));
            }
            throw new RuntimeException("not implemented: comparator operator " + ctx.op.getText());
        }

        @Override
        public Object visitBinaryExpression(TriggerParser.BinaryExpressionContext ctx) {
            if (ctx.op.AND() != null) {
                return (Boolean)visit(ctx.left) && (Boolean) visit(ctx.right);
            }
            else if (ctx.op.OR() != null) {
                return (Boolean)visit(ctx.left) || (Boolean) visit(ctx.right);
            }
            throw new RuntimeException("not implemented: binary operator " + ctx.op.getText());
        }

        @Override
        public Integer visitInt(TriggerParser.IntContext ctx) {
            return Integer.valueOf(ctx.INT().getText());
        }

        @Override
        public Object visitBoolExpression(TriggerParser.BoolExpressionContext ctx) {
            return "TRUE".equals(ctx.getText());
        }

        @Override
        public Object visitMonstr(TriggerParser.MonstrContext ctx) {
            System.out.println(ctx.getText());
            // FIXME 这里增加查询opentsdb的逻辑，然后要返回数据，数据可以是数字或者字符串
            // 先返回字符串
            return 2;
        }

        private static double asDouble(Object obj) {
            return NumberUtils.toDouble(obj.toString());
        }

    }
}
