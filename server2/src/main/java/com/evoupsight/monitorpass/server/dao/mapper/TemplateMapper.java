package com.evoupsight.monitorpass.server.dao.mapper;

import com.evoupsight.monitorpass.server.dao.model.Template;
import com.evoupsight.monitorpass.server.dao.model.TemplateExample;
import java.util.List;
import org.apache.ibatis.annotations.Param;

public interface TemplateMapper {
    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    long countByExample(TemplateExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int deleteByExample(TemplateExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int deleteByPrimaryKey(Long id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int insert(Template record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int insertSelective(Template record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    List<Template> selectByExampleSelective(@Param("example") TemplateExample example, @Param("selective") Template.Column ... selective);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    List<Template> selectByExample(TemplateExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    Template selectByPrimaryKeySelective(@Param("id") Long id, @Param("selective") Template.Column ... selective);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    Template selectByPrimaryKey(Long id);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int updateByExampleSelective(@Param("record") Template record, @Param("example") TemplateExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int updateByExample(@Param("record") Template record, @Param("example") TemplateExample example);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int updateByPrimaryKeySelective(Template record);

    /**
     * This method was generated by MyBatis Generator.
     * This method corresponds to the database table template
     *
     * @mbg.generated
     */
    int updateByPrimaryKey(Template record);
}