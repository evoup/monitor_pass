package com.evoupsight.monitorpass.datacollector.dao.mapper;

import com.evoupsight.monitorpass.datacollector.dao.model.Server;
import com.evoupsight.monitorpass.datacollector.dao.model.ServerExample;
import java.util.List;
import org.apache.ibatis.annotations.Param;

public interface ServerMapper {
    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    long countByExample(ServerExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int deleteByExample(ServerExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int deleteByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int insert(Server record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int insertSelective(Server record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    List<Server> selectByExample(ServerExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    Server selectByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int updateByExampleSelective(@Param("record") Server record, @Param("example") ServerExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int updateByExample(@Param("record") Server record, @Param("example") ServerExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int updateByPrimaryKeySelective(Server record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server
     *
     * @mbg.generated Thu Sep 05 12:21:48 CST 2019
     */
    int updateByPrimaryKey(Server record);
}