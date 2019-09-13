package com.evoupsight.monitorpass.server.dao.model;

import java.util.ArrayList;
import java.util.Arrays;

public class Trigger {
    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column trigger.id
     *
     * @mbg.generated
     */
    private Long id;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column trigger.expression
     *
     * @mbg.generated
     */
    private String expression;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column trigger.name
     *
     * @mbg.generated
     */
    private String name;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column trigger.desc
     *
     * @mbg.generated
     */
    private String desc;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column trigger.trigger_copy_from
     *
     * @mbg.generated
     */
    private Integer triggerCopyFrom;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column trigger.template_id
     *
     * @mbg.generated
     */
    private Long templateId;

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column trigger.id
     *
     * @return the value of trigger.id
     *
     * @mbg.generated
     */
    public Long getId() {
        return id;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column trigger.id
     *
     * @param id the value for trigger.id
     *
     * @mbg.generated
     */
    public void setId(Long id) {
        this.id = id;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column trigger.expression
     *
     * @return the value of trigger.expression
     *
     * @mbg.generated
     */
    public String getExpression() {
        return expression;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column trigger.expression
     *
     * @param expression the value for trigger.expression
     *
     * @mbg.generated
     */
    public void setExpression(String expression) {
        this.expression = expression;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column trigger.name
     *
     * @return the value of trigger.name
     *
     * @mbg.generated
     */
    public String getName() {
        return name;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column trigger.name
     *
     * @param name the value for trigger.name
     *
     * @mbg.generated
     */
    public void setName(String name) {
        this.name = name;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column trigger.desc
     *
     * @return the value of trigger.desc
     *
     * @mbg.generated
     */
    public String getDesc() {
        return desc;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column trigger.desc
     *
     * @param desc the value for trigger.desc
     *
     * @mbg.generated
     */
    public void setDesc(String desc) {
        this.desc = desc;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column trigger.trigger_copy_from
     *
     * @return the value of trigger.trigger_copy_from
     *
     * @mbg.generated
     */
    public Integer getTriggerCopyFrom() {
        return triggerCopyFrom;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column trigger.trigger_copy_from
     *
     * @param triggerCopyFrom the value for trigger.trigger_copy_from
     *
     * @mbg.generated
     */
    public void setTriggerCopyFrom(Integer triggerCopyFrom) {
        this.triggerCopyFrom = triggerCopyFrom;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column trigger.template_id
     *
     * @return the value of trigger.template_id
     *
     * @mbg.generated
     */
    public Long getTemplateId() {
        return templateId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column trigger.template_id
     *
     * @param templateId the value for trigger.template_id
     *
     * @mbg.generated
     */
    public void setTemplateId(Long templateId) {
        this.templateId = templateId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table trigger
     *
     * @mbg.generated
     */
    public static Trigger.Builder builder() {
        return new Trigger.Builder();
    }

    /**
     * This class was generated by MyBatis Generator.
     * This class corresponds to the database table trigger
     *
     * @mbg.generated
     */
    public static class Builder {
        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table trigger
         *
         * @mbg.generated
         */
        private Trigger obj;

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public Builder() {
            this.obj = new Trigger();
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column trigger.id
         *
         * @param id the value for trigger.id
         *
         * @mbg.generated
         */
        public Builder id(Long id) {
            obj.setId(id);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column trigger.expression
         *
         * @param expression the value for trigger.expression
         *
         * @mbg.generated
         */
        public Builder expression(String expression) {
            obj.setExpression(expression);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column trigger.name
         *
         * @param name the value for trigger.name
         *
         * @mbg.generated
         */
        public Builder name(String name) {
            obj.setName(name);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column trigger.desc
         *
         * @param desc the value for trigger.desc
         *
         * @mbg.generated
         */
        public Builder desc(String desc) {
            obj.setDesc(desc);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column trigger.trigger_copy_from
         *
         * @param triggerCopyFrom the value for trigger.trigger_copy_from
         *
         * @mbg.generated
         */
        public Builder triggerCopyFrom(Integer triggerCopyFrom) {
            obj.setTriggerCopyFrom(triggerCopyFrom);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column trigger.template_id
         *
         * @param templateId the value for trigger.template_id
         *
         * @mbg.generated
         */
        public Builder templateId(Long templateId) {
            obj.setTemplateId(templateId);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public Trigger build() {
            return this.obj;
        }
    }

    /**
     * This enum was generated by MyBatis Generator.
     * This enum corresponds to the database table trigger
     *
     * @mbg.generated
     */
    public enum Column {
        id("id", "id", "BIGINT", false),
        expression("expression", "expression", "VARCHAR", false),
        name("name", "name", "VARCHAR", true),
        desc("desc", "desc", "VARCHAR", true),
        triggerCopyFrom("trigger_copy_from", "triggerCopyFrom", "INTEGER", false),
        templateId("template_id", "templateId", "BIGINT", false);

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table trigger
         *
         * @mbg.generated
         */
        private static final String BEGINNING_DELIMITER = "`";

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table trigger
         *
         * @mbg.generated
         */
        private static final String ENDING_DELIMITER = "`";

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table trigger
         *
         * @mbg.generated
         */
        private final String column;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table trigger
         *
         * @mbg.generated
         */
        private final boolean isColumnNameDelimited;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table trigger
         *
         * @mbg.generated
         */
        private final String javaProperty;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table trigger
         *
         * @mbg.generated
         */
        private final String jdbcType;

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String value() {
            return this.column;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String getValue() {
            return this.column;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String getJavaProperty() {
            return this.javaProperty;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String getJdbcType() {
            return this.jdbcType;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        Column(String column, String javaProperty, String jdbcType, boolean isColumnNameDelimited) {
            this.column = column;
            this.javaProperty = javaProperty;
            this.jdbcType = jdbcType;
            this.isColumnNameDelimited = isColumnNameDelimited;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String desc() {
            return this.getEscapedColumnName() + " DESC";
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String asc() {
            return this.getEscapedColumnName() + " ASC";
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public static Column[] excludes(Column ... excludes) {
            ArrayList<Column> columns = new ArrayList<>(Arrays.asList(Column.values()));
            if (excludes != null && excludes.length > 0) {
                columns.removeAll(new ArrayList<>(Arrays.asList(excludes)));
            }
            return columns.toArray(new Column[]{});
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String getEscapedColumnName() {
            if (this.isColumnNameDelimited) {
                return new StringBuilder().append(BEGINNING_DELIMITER).append(this.column).append(ENDING_DELIMITER).toString();
            } else {
                return this.column;
            }
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table trigger
         *
         * @mbg.generated
         */
        public String getAliasedEscapedColumnName() {
            return this.getEscapedColumnName();
        }
    }
}