<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="操作名称：">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入操作名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="通知主题：">
        <el-col :span="8">
          <el-input v-model="form.subject" placeholder="请输入通知主题"/>
        </el-col>
      </el-form-item>
      <el-form-item label="通知内容：">
        <el-col :span="8">
          <el-input
            v-model="form.message"
            class="note"
            placeholder="请输入通知内容"
            type="textarea"
          />
        </el-col>
      </el-form-item>
      <el-form-item
        label="请选择模板："
      >
        <el-col :span="10">
          <el-select
            v-model="templateSelectModel"
            placeholder="请选择模板"
            style="width: 80%"
            @change="fetchTriggerListData"
          >
            <el-option
              v-for="item in templateListData"
              :key="item.id"
              :label="item.name"
              :aria-selected="true"
              :value="item.id"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item
        label="请选择触发器："
      >
        <el-col :span="10">
          <el-select
            v-model="triggerSelectModel"
            placeholder="请选择触发器"
            style="width: 80%"
          >
            <el-option
              v-for="item in triggerListData"
              :key="item.id"
              :label="item.trigger_name"
              :aria-selected="true"
              :value="item.id"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-col :span="10">
          <el-radio v-model="runTypeModel" label="1" @change="changeData('send_notice')">发送消息</el-radio>
          <el-radio v-model="runTypeModel" label="2" @change="changeData('exec_command')">执行命令</el-radio>
        </el-col>
      </el-form-item>
      <el-col :span="10">
        <component ref="operationFormComp" :is="componentFile" />
      </el-col>
    </el-form>
  </div>
</template>

<script>
import { template_list } from '../../api/template'
import { trigger_list } from '../../api/trigger'

export default {
  name: 'AddOperation',
  data() {
    return {
      form: {
        name: '',
        subject: '',
        message: ''
      },
      templateListData: [],
      templateSelectModel: null,
      triggerListData: [],
      triggerSelectModel: null,
      runTypeModel: '1'
    }
  },
  // 动态加载组件
  computed: {
    componentFile() {
      const componentName = this.$store.state.operationTypeComponentName
      return () => import(`./components/${componentName}.vue`)
    }
  },
  created() {
    this.fetchTemplateListData()
  },
  methods: {
    changeData(d) {
      this.$store.state.operationTypeComponentName = d
    },
    // 获取所有模板列表
    fetchTemplateListData() {
      template_list({ page: 1, size: 99999, order: 'asc' }).then(response => {
        this.templateListData = response.data.items
      })
    },
    fetchTriggerListData() {
      trigger_list({ template_id: this.templateSelectModel }).then(response => {
        this.triggerListData = response.data.items
      })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ .el-form-item {
    margin-bottom: 5px;
  }
  .app-container /deep/ textarea {
    height:120px;
  }
</style>
