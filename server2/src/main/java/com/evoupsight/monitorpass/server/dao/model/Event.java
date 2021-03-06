package com.evoupsight.monitorpass.server.dao.model;

import java.util.ArrayList;
import java.util.Arrays;

public class Event {
    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column event.id
     *
     * @mbg.generated
     */
    private Long id;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column event.event
     *
     * @mbg.generated
     */
    private String event;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column event.time
     *
     * @mbg.generated
     */
    private Integer time;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column event.type
     *
     * @mbg.generated
     */
    private Integer type;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column event.acknowledged
     *
     * @mbg.generated
     */
    private Boolean acknowledged;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column event.acknowledge
     *
     * @mbg.generated
     */
    private String acknowledge;

    /**
     *
     * This field was generated by MyBatis Generator.
     * This field corresponds to the database column event.target_id
     *
     * @mbg.generated
     */
    private Integer targetId;

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column event.id
     *
     * @return the value of event.id
     *
     * @mbg.generated
     */
    public Long getId() {
        return id;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column event.id
     *
     * @param id the value for event.id
     *
     * @mbg.generated
     */
    public void setId(Long id) {
        this.id = id;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column event.event
     *
     * @return the value of event.event
     *
     * @mbg.generated
     */
    public String getEvent() {
        return event;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column event.event
     *
     * @param event the value for event.event
     *
     * @mbg.generated
     */
    public void setEvent(String event) {
        this.event = event;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column event.time
     *
     * @return the value of event.time
     *
     * @mbg.generated
     */
    public Integer getTime() {
        return time;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column event.time
     *
     * @param time the value for event.time
     *
     * @mbg.generated
     */
    public void setTime(Integer time) {
        this.time = time;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column event.type
     *
     * @return the value of event.type
     *
     * @mbg.generated
     */
    public Integer getType() {
        return type;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column event.type
     *
     * @param type the value for event.type
     *
     * @mbg.generated
     */
    public void setType(Integer type) {
        this.type = type;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column event.acknowledged
     *
     * @return the value of event.acknowledged
     *
     * @mbg.generated
     */
    public Boolean getAcknowledged() {
        return acknowledged;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column event.acknowledged
     *
     * @param acknowledged the value for event.acknowledged
     *
     * @mbg.generated
     */
    public void setAcknowledged(Boolean acknowledged) {
        this.acknowledged = acknowledged;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column event.acknowledge
     *
     * @return the value of event.acknowledge
     *
     * @mbg.generated
     */
    public String getAcknowledge() {
        return acknowledge;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column event.acknowledge
     *
     * @param acknowledge the value for event.acknowledge
     *
     * @mbg.generated
     */
    public void setAcknowledge(String acknowledge) {
        this.acknowledge = acknowledge;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method returns the value of the database column event.target_id
     *
     * @return the value of event.target_id
     *
     * @mbg.generated
     */
    public Integer getTargetId() {
        return targetId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method sets the value of the database column event.target_id
     *
     * @param targetId the value for event.target_id
     *
     * @mbg.generated
     */
    public void setTargetId(Integer targetId) {
        this.targetId = targetId;
    }

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table event
     *
     * @mbg.generated
     */
    public static Event.Builder builder() {
        return new Event.Builder();
    }

    /**
     * This class was generated by MyBatis Generator.
     * This class corresponds to the database table event
     *
     * @mbg.generated
     */
    public static class Builder {
        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table event
         *
         * @mbg.generated
         */
        private Event obj;

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public Builder() {
            this.obj = new Event();
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column event.id
         *
         * @param id the value for event.id
         *
         * @mbg.generated
         */
        public Builder id(Long id) {
            obj.setId(id);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column event.event
         *
         * @param event the value for event.event
         *
         * @mbg.generated
         */
        public Builder event(String event) {
            obj.setEvent(event);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column event.time
         *
         * @param time the value for event.time
         *
         * @mbg.generated
         */
        public Builder time(Integer time) {
            obj.setTime(time);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column event.type
         *
         * @param type the value for event.type
         *
         * @mbg.generated
         */
        public Builder type(Integer type) {
            obj.setType(type);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column event.acknowledge
         *
         * @param acknowledge the value for event.acknowledge
         *
         * @mbg.generated
         */
        public Builder acknowledge(String acknowledge) {
            obj.setAcknowledge(acknowledge);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column event.acknowledged
         *
         * @param acknowledged the value for event.acknowledged
         *
         * @mbg.generated
         */
        public Builder acknowledged(Boolean acknowledged) {
            obj.setAcknowledged(acknowledged);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method sets the value of the database column event.target_id
         *
         * @param targetId the value for event.target_id
         *
         * @mbg.generated
         */
        public Builder targetId(Integer targetId) {
            obj.setTargetId(targetId);
            return this;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public Event build() {
            return this.obj;
        }
    }

    /**
     * This enum was generated by MyBatis Generator.
     * This enum corresponds to the database table event
     *
     * @mbg.generated
     */
    public enum Column {
        id("id", "id", "BIGINT", false),
        event("event", "event", "VARCHAR", false),
        time("time", "time", "INTEGER", true),
        type("type", "type", "INTEGER", true),
        acknowledged("acknowledged", "acknowledged", "BIT", false),
        acknowledge("acknowledge", "acknowledge", "VARCHAR", false),
        targetId("target_id", "targetId", "INTEGER", false);

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table event
         *
         * @mbg.generated
         */
        private static final String BEGINNING_DELIMITER = "`";

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table event
         *
         * @mbg.generated
         */
        private static final String ENDING_DELIMITER = "`";

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table event
         *
         * @mbg.generated
         */
        private final String column;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table event
         *
         * @mbg.generated
         */
        private final boolean isColumnNameDelimited;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table event
         *
         * @mbg.generated
         */
        private final String javaProperty;

        /**
         * This field was generated by MyBatis Generator.
         * This field corresponds to the database table event
         *
         * @mbg.generated
         */
        private final String jdbcType;

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public String value() {
            return this.column;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public String getValue() {
            return this.column;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public String getJavaProperty() {
            return this.javaProperty;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public String getJdbcType() {
            return this.jdbcType;
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
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
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public String desc() {
            return this.getEscapedColumnName() + " DESC";
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public String asc() {
            return this.getEscapedColumnName() + " ASC";
        }

        /**
         * This method was generated by MyBatis Generator.
         * This method corresponds to the database table event
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
         * This method corresponds to the database table event
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
         * This method corresponds to the database table event
         *
         * @mbg.generated
         */
        public String getAliasedEscapedColumnName() {
            return this.getEscapedColumnName();
        }
    }
}