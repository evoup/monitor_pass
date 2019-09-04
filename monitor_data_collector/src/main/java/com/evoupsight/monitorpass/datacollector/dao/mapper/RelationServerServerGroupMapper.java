package com.evoupsight.monitorpass.datacollector.dao.mapper;

import com.evoupsight.monitorpass.datacollector.dao.model.RelationServerServerGroup;
import com.evoupsight.monitorpass.datacollector.dao.model.RelationServerServerGroupExample;
import java.util.List;
import org.apache.ibatis.annotations.Param;

public interface RelationServerServerGroupMapper {
    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    long countByExample(RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int deleteByExample(RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int deleteByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int insert(RelationServerServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int insertSelective(RelationServerServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    List<RelationServerServerGroup> selectByExample(RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    RelationServerServerGroup selectByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByExampleSelective(@Param("record") RelationServerServerGroup record, @Param("example") RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByExample(@Param("record") RelationServerServerGroup record, @Param("example") RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByPrimaryKeySelective(RelationServerServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated Thu Sep 05 00:09:49 CST 2019
     */
    int updateByPrimaryKey(RelationServerServerGroup record);
}