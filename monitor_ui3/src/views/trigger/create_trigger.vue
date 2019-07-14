<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="触发器名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入触发器名称" />
        </el-col>
      </el-form-item>
      <el-form-item label="表达式">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-input
                v-model="form.expression"
                placeholder="请输入表达式"
                type="textarea"
              />
            </el-col>
            <el-col :span="10">
              <el-button
                type="primary"
                @click="addCondition"
              >添加条件</el-button
              >
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-input
                v-model="form.desc"
                placeholder="请输入描述"
                type="textarea"
              />
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item label="严重等级">
        <el-col :span="8">
          <el-select v-model="optionValue" placeholder="请选择">
            <el-option
              v-for="(item, index) in options"
              :key="index"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="只告警一次">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-switch
                v-model="switchValue"
                active-value="1"
                inactive-value="0"
              />
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item label="启用">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-switch
                v-model="switchValue1"
                active-value="1"
                inactive-value="0"
              />
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            addServer(form.name, form.client, form.jmx, form.snmp, form.idc)
          "
        >创建</el-button
        >
        <el-button @click="jumpTriggerList">取消</el-button>
      </el-form-item>
      <!--浮层对话框-->
      <el-dialog :visible.sync="dialogFormVisible" title="添加触发器表达式条件">
        <el-form :model="form">
          <el-form-item
            :label-width="formLabelWidth"
            label="选择模板或服务器组"
          >
            <el-radio-group v-model="radio" @change="selectType">
              <el-radio-button label="template">模板</el-radio-button>
              <el-radio-button label="server_group">服务器组</el-radio-button>
            </el-radio-group>
          </el-form-item>
          <el-form-item
            :label-width="formLabelWidth"
            :label="selectTemplateOrServerGroup"
          >
            <el-select
              v-model="templateSelectModel"
              placeholder="请选择模板（可选）"
              style="width: 80%"
            >
              <el-option
                v-for="item in templateData"
                :key="item.id"
                :label="item.name"
                :aria-selected="true"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">取 消</el-button>
          <el-button
            type="primary"
            @click="dialogFormVisible = false"
          >确 定</el-button
          >
        </div>
      </el-dialog>
    </el-form>
  </div>
</template>

<script>
import { template_list } from '../../api/template'
export default {
  name: 'CreateTrigger',
  data() {
    return {
      form: {
        name: '',
        expression: '',
        desc: '',
        level: 0
      },
      options: [
        {
          value: '1',
          label: '信息'
        },
        {
          value: '2',
          label: '警告'
        },
        {
          value: '3',
          label: '严重警告'
        },
        {
          value: '4',
          label: '灾难警告'
        }
      ],
      optionValue: '2',
      switchValue: '1',
      switchValue1: '1',
      dialogFormVisible: false,
      formLabelWidth: '150px',
      templateSelectModel: [],
      templateData: [],
      radio: 'template',
      selectTemplateOrServerGroup: '模板'
    }
  },
  methods: {
    addCondition() {
      // 打开对话框
      this.dialogFormVisible = true
      this.fetchData()
    },
    // 获取所有模板列表
    fetchTemplateListData() {
      template_list().then(response => {
        this.templateData = response.data.items
        this.templateSelectModel = 1
      })
    },
    fetchData() {
      this.fetchTemplateListData()
    },
    selectType(value) {
      if (value === 'template') {
        this.selectTemplateOrServerGroup = '模板'
      } else {
        this.selectTemplateOrServerGroup = '服务器组'
      }
    },
    jumpTriggerList() {
      this.$router.go(-1)
    }
  }
}
</script>

<style scoped>
.el-col {
  border-radius: 4px;
}
</style>
