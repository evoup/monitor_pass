package com.evoupsight.monitorpass.datacollector.dao.mapper;

import com.evoupsight.monitorpass.datacollector.dao.model.ServerGroup;
import com.evoupsight.monitorpass.datacollector.dao.model.ServerGroupExample;
import java.util.List;
import org.apache.ibatis.annotations.Param;

public interface ServerGroupMapper {
    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    long countByExample(ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int deleteByExample(ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int deleteByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int insert(ServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int insertSelective(ServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    List<ServerGroup> selectByExample(ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    ServerGroup selectByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByExampleSelective(@Param("record") ServerGroup record, @Param("example") ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByExample(@Param("record") ServerGroup record, @Param("example") ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByPrimaryKeySelective(ServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByPrimaryKey(ServerGroup record);
}