package com.evoupsight.monitorpass.server.dao.model;

import java.util.ArrayList;
import java.util.Arrays;

public class Item {
    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.id
     *
     * @mbg.generated
     */
    private Integer id;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.name
     *
     * @mbg.generated
     */
    private String name;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.data_type
     *
     * @mbg.generated
     */
    private Byte dataType;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.delay
     *
     * @mbg.generated
     */
    private Integer delay;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.desc
     *
     * @mbg.generated
     */
    private String desc;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.error
     *
     * @mbg.generated
     */
    private String error;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.key
     *
     * @mbg.generated
     */
    private String key;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.multiplier
     *
     * @mbg.generated
     */
    private Double multiplier;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.unit
     *
     * @mbg.generated
     */
    private String unit;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.host_id
     *
     * @mbg.generated
     */
    private Integer hostId;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.template_id
     *
     * @mbg.generated
     */
    private Integer templateId;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column item.delta
     *
     * @mbg.generated
     */
    private Integer delta;

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.id
     *
     * @return the value of item.id
     *
     * @mbg.generated
     */
    public Integer getId() {
        return id;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.id
     *
     * @param id the value for item.id
     *
     * @mbg.generated
     */
    public void setId(Integer id) {
        this.id = id;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.name
     *
     * @return the value of item.name
     *
     * @mbg.generated
     */
    public String getName() {
        return name;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.name
     *
     * @param name the value for item.name
     *
     * @mbg.generated
     */
    public void setName(String name) {
        this.name = name;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.data_type
     *
     * @return the value of item.data_type
     *
     * @mbg.generated
     */
    public Byte getDataType() {
        return dataType;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.data_type
     *
     * @param dataType the value for item.data_type
     *
     * @mbg.generated
     */
    public void setDataType(Byte dataType) {
        this.dataType = dataType;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.delay
     *
     * @return the value of item.delay
     *
     * @mbg.generated
     */
    public Integer getDelay() {
        return delay;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.delay
     *
     * @param delay the value for item.delay
     *
     * @mbg.generated
     */
    public void setDelay(Integer delay) {
        this.delay = delay;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.desc
     *
     * @return the value of item.desc
     *
     * @mbg.generated
     */
    public String getDesc() {
        return desc;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.desc
     *
     * @param desc the value for item.desc
     *
     * @mbg.generated
     */
    public void setDesc(String desc) {
        this.desc = desc;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.error
     *
     * @return the value of item.error
     *
     * @mbg.generated
     */
    public String getError() {
        return error;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.error
     *
     * @param error the value for item.error
     *
     * @mbg.generated
     */
    public void setError(String error) {
        this.error = error;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.key
     *
     * @return the value of item.key
     *
     * @mbg.generated
     */
    public String getKey() {
        return key;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.key
     *
     * @param key the value for item.key
     *
     * @mbg.generated
     */
    public void setKey(String key) {
        this.key = key;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.multiplier
     *
     * @return the value of item.multiplier
     *
     * @mbg.generated
     */
    public Double getMultiplier() {
        return multiplier;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.multiplier
     *
     * @param multiplier the value for item.multiplier
     *
     * @mbg.generated
     */
    public void setMultiplier(Double multiplier) {
        this.multiplier = multiplier;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.unit
     *
     * @return the value of item.unit
     *
     * @mbg.generated
     */
    public String getUnit() {
        return unit;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.unit
     *
     * @param unit the value for item.unit
     *
     * @mbg.generated
     */
    public void setUnit(String unit) {
        this.unit = unit;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.host_id
     *
     * @return the value of item.host_id
     *
     * @mbg.generated
     */
    public Integer getHostId() {
        return hostId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.host_id
     *
     * @param hostId the value for item.host_id
     *
     * @mbg.generated
     */
    public void setHostId(Integer hostId) {
        this.hostId = hostId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.template_id
     *
     * @return the value of item.template_id
     *
     * @mbg.generated
     */
    public Integer getTemplateId() {
        return templateId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.template_id
     *
     * @param templateId the value for item.template_id
     *
     * @mbg.generated
     */
    public void setTemplateId(Integer templateId) {
        this.templateId = templateId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column item.delta
     *
     * @return the value of item.delta
     *
     * @mbg.generated
     */
    public Integer getDelta() {
        return delta;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column item.delta
     *
     * @param delta the value for item.delta
     *
     * @mbg.generated
     */
    public void setDelta(Integer delta) {
        this.delta = delta;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table item
     *
     * @mbg.generated
     */
    public static Item.Builder builder() {
        return new Item.Builder();
    }

    /**
     * This class was generated by MyBatis Generator.
     * This class corresponds to the database table item
     *
     * @mbg.generated
     */
    public static class Builder {
        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table item
         *
         * @mbg.generated
         */
        private Item obj;

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public Builder() {
            this.obj = new Item();
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.id
         *
         * @param id the value for item.id
         *
         * @mbg.generated
         */
        public Builder id(Integer id) {
            obj.setId(id);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.name
         *
         * @param name the value for item.name
         *
         * @mbg.generated
         */
        public Builder name(String name) {
            obj.setName(name);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.data_type
         *
         * @param dataType the value for item.data_type
         *
         * @mbg.generated
         */
        public Builder dataType(Byte dataType) {
            obj.setDataType(dataType);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.delay
         *
         * @param delay the value for item.delay
         *
         * @mbg.generated
         */
        public Builder delay(Integer delay) {
            obj.setDelay(delay);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.desc
         *
         * @param desc the value for item.desc
         *
         * @mbg.generated
         */
        public Builder desc(String desc) {
            obj.setDesc(desc);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.error
         *
         * @param error the value for item.error
         *
         * @mbg.generated
         */
        public Builder error(String error) {
            obj.setError(error);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.key
         *
         * @param key the value for item.key
         *
         * @mbg.generated
         */
        public Builder key(String key) {
            obj.setKey(key);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.multiplier
         *
         * @param multiplier the value for item.multiplier
         *
         * @mbg.generated
         */
        public Builder multiplier(Double multiplier) {
            obj.setMultiplier(multiplier);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.unit
         *
         * @param unit the value for item.unit
         *
         * @mbg.generated
         */
        public Builder unit(String unit) {
            obj.setUnit(unit);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.host_id
         *
         * @param hostId the value for item.host_id
         *
         * @mbg.generated
         */
        public Builder hostId(Integer hostId) {
            obj.setHostId(hostId);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.template_id
         *
         * @param templateId the value for item.template_id
         *
         * @mbg.generated
         */
        public Builder templateId(Integer templateId) {
            obj.setTemplateId(templateId);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column item.delta
         *
         * @param delta the value for item.delta
         *
         * @mbg.generated
         */
        public Builder delta(Integer delta) {
            obj.setDelta(delta);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public Item build() {
            return this.obj;
        }
    }

    /**
     * This enum was generated by MyBatis Generator.
     * This enum corresponds to the database table item
     *
     * @mbg.generated
     */
    public enum Column {
        id("id", "id", "INTEGER", false),
        name("name", "name", "VARCHAR", true),
        dataType("data_type", "dataType", "TINYINT", false),
        delay("delay", "delay", "INTEGER", false),
        desc("desc", "desc", "VARCHAR", true),
        error("error", "error", "VARCHAR", false),
        key("key", "key", "VARCHAR", true),
        multiplier("multiplier", "multiplier", "DOUBLE", false),
        unit("unit", "unit", "VARCHAR", false),
        hostId("host_id", "hostId", "INTEGER", false),
        templateId("template_id", "templateId", "INTEGER", false),
        delta("delta", "delta", "INTEGER", false);

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table item
         *
         * @mbg.generated
         */
        private static final String BEGINNING_DELIMITER = "`";

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table item
         *
         * @mbg.generated
         */
        private static final String ENDING_DELIMITER = "`";

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table item
         *
         * @mbg.generated
         */
        private final String column;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table item
         *
         * @mbg.generated
         */
        private final boolean isColumnNameDelimited;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table item
         *
         * @mbg.generated
         */
        private final String javaProperty;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table item
         *
         * @mbg.generated
         */
        private final String jdbcType;

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public String value() {
            return this.column;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public String getValue() {
            return this.column;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public String getJavaProperty() {
            return this.javaProperty;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public String getJdbcType() {
            return this.jdbcType;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
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
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public String desc() {
            return this.getEscapedColumnName() + " DESC";
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public String asc() {
            return this.getEscapedColumnName() + " ASC";
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table item
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
         * This method corresponds to the database table item
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
         * This method corresponds to the database table item
         *
         * @mbg.generated
         */
        public String getAliasedEscapedColumnName() {
            return this.getEscapedColumnName();
        }
    }
}