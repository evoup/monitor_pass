package com.evoupsight.monitorpass.server.dao.mapper;

import com.evoupsight.monitorpass.server.dao.model.RelationServerServerGroup;
import com.evoupsight.monitorpass.server.dao.model.RelationServerServerGroupExample;
import java.util.List;
import org.apache.ibatis.annotations.Param;

public interface RelationServerServerGroupMapper {
    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    long countByExample(RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int deleteByExample(RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int deleteByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int insert(RelationServerServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int insertSelective(RelationServerServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    List<RelationServerServerGroup> selectByExampleSelective(@Param("example") RelationServerServerGroupExample example, @Param("selective") RelationServerServerGroup.Column ... selective);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    List<RelationServerServerGroup> selectByExample(RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    RelationServerServerGroup selectByPrimaryKeySelective(@Param("id") Integer id, @Param("selective") RelationServerServerGroup.Column ... selective);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    RelationServerServerGroup selectByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int updateByExampleSelective(@Param("record") RelationServerServerGroup record, @Param("example") RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int updateByExample(@Param("record") RelationServerServerGroup record, @Param("example") RelationServerServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int updateByPrimaryKeySelective(RelationServerServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table r_server_server_group
     *
     * @mbg.generated
     */
    int updateByPrimaryKey(RelationServerServerGroup record);
}