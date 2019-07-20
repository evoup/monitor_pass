<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="触发器名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入触发器名称"/>
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
                v-if="form.expression.length<=0"
                type="primary"
                @click="addCondition"
              >添加条件
              </el-button
              >
            </el-col>
            <el-col :span="10">
              <el-button
                v-if="form.expression.length>0"
                type="primary"
                @click="addCondition"
              >添加AND条件
              </el-button
              >
              <el-button
                v-if="form.expression.length>0"
                type="primary"
                @click="addCondition"
              >添加OR条件
              </el-button
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
        >创建
        </el-button
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
              v-model="templateOrServerGroupSelectModel"
              placeholder=""
              style="width: 80%"
            >
              <el-option
                v-for="item in templateOrServerGroupData"
                :key="item.id"
                :label="item.name"
                :aria-selected="true"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item
            :label-width="formLabelWidth"
            label="请选择监控项"
          >
            <el-select
              v-model="ItemSelectModel"
              placeholder="请选择监控项"
              style="width: 80%"
            >
              <el-option
                v-for="item in monitorItemListData"
                :key="item.key"
                :label="item.name"
                :aria-selected="true"
                :value="item"
              >
                <span style="float: left">{{ item.name }}</span>
                <span style="float: right; color: #8492a6; font-size: 13px;padding-left: 20px">键：{{ item.key }}</span>
              </el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <el-form-item
          :label-width="formLabelWidth"
          label="请选择函数"
        >
          <el-select
            v-model="functionSelectModel"
            placeholder=""
            style="width: 80%"
          >
            <el-option
              v-for="(item, index) in functionOptions"
              :key="index"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <person-form ref="personFormComp"/>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">取 消</el-button>
          <el-button
            type="primary"
            @click="addExpression(ItemSelectModel.key)"
          >确 定
          </el-button
          >
        </div>
      </el-dialog>
    </el-form>
  </div>
</template>

<script>
import { template_list } from '../../api/template'
import { server_group_list } from '../../api/server'
import { item_list } from '../../api/item'
import PersonForm from './components/last_eq'

export default {
  name: 'CreateTrigger',
  components: {
    'person-form': PersonForm
  },
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
      functionOptions: [{
        value: 'last[=]',
        label: '最末(最近) T值是 = N'
      }],
      // 默认严重等级
      optionValue: '2',
      // 默认只告警一次
      switchValue: '1',
      // 默认启用
      switchValue1: '1',
      dialogFormVisible: false,
      templateSelectVisible: true,
      formLabelWidth: '150px',
      templateOrServerGroupSelectModel: [],
      ItemSelectModel: [],
      // 默认函数
      functionSelectModel: 'last[=]',
      templateOrServerGroupData: [],
      monitorItemListData: [],
      functionListData: [],
      radio: 'template',
      selectTemplateOrServerGroup: '请选择模板'
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
        this.templateOrServerGroupData = response.data.items
        this.templateOrServerGroupSelectModel = 1
        // 获取完了刷新监控项
        this.fetchMonitorItemListData()
      })
    },
    // 获取服务器组列表
    fetchServerGroupListData() {
      server_group_list().then(response => {
        this.templateOrServerGroupData = response.data.items
      })
    },
    // 获取监控项
    fetchMonitorItemListData() {
      item_list({ size: 10000, template_id: this.templateOrServerGroupSelectModel }).then(response => {
        this.monitorItemListData = response.data.items
        // this.ItemSelectModel = 1
        this.ItemSelectModel = this.monitorItemListData[0]
      })
    },
    fetchData() {
      this.fetchTemplateListData()
      this.fetchServerGroupListData()
    },
    selectType(value) {
      if (value === 'template') {
        this.selectTemplateOrServerGroup = '请选择模板'
        this.fetchTemplateListData()
      } else {
        this.selectTemplateOrServerGroup = '请选择服务器组'
        this.fetchServerGroupListData()
      }
    },
    jumpTriggerList() {
      this.$router.go(-1)
    },
    addExpression(expression) {
      this.dialogFormVisible = false
      this.form.expression = expression
    }
  }
}
</script>

<style scoped>
  .el-col {
    border-radius: 4px;
  }
</style>
