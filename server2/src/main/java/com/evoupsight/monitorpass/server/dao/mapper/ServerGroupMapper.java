package com.evoupsight.monitorpass.server.dao.mapper;

import com.evoupsight.monitorpass.server.dao.model.ServerGroup;
import com.evoupsight.monitorpass.server.dao.model.ServerGroupExample;
import java.util.List;
import org.apache.ibatis.annotations.Param;

public interface ServerGroupMapper {
    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    long countByExample(ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int deleteByExample(ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int deleteByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int insert(ServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int insertSelective(ServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    List<ServerGroup> selectByExampleSelective(@Param("example") ServerGroupExample example, @Param("selective") ServerGroup.Column ... selective);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    List<ServerGroup> selectByExample(ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    ServerGroup selectByPrimaryKeySelective(@Param("id") Integer id, @Param("selective") ServerGroup.Column ... selective);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    ServerGroup selectByPrimaryKey(Integer id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int updateByExampleSelective(@Param("record") ServerGroup record, @Param("example") ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int updateByExample(@Param("record") ServerGroup record, @Param("example") ServerGroupExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int updateByPrimaryKeySelective(ServerGroup record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table server_group
     *
     * @mbg.generated
     */
    int updateByPrimaryKey(ServerGroup record);
}