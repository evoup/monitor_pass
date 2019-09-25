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
        <el-button
          type="primary"
          @click="
            addOperation(form.name,form.subject,form.message, triggerSelectModel)
          "
        >创建
        </el-button
        >
        <el-button @click="jumpOperationList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { template_list } from '../../api/template'
import { trigger_list } from '../../api/trigger'
import { add_operation, read_operation } from '../../api/operation'

export default {
  name: 'AddOperation',
  data() {
    return {
      form: {
        name: '',
        subject: null,
        message: null },
      templateListData: [],
      templateSelectModel: null,
      triggerListData: [],
      triggerSelectModel: null
    }
  },
  created() {
    this.fetchData(this.$route.query.id)
    this.fetchTemplateListData()
  },
  methods: {
    fetchData(id) {
      read_operation({ id: id }).then(response => {
        this.form.name = response.data.item.name
        this.form.subject = response.data.item.title
        this.form.message = response.data.item.content
      })
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
    },
    addOperation(a, b, c, d) {
      add_operation(a, b, c, d).then(response => {

      }).catch(e => {
        console.log(e)
      })
    },
    jumpOperationList() {
      this.$router.push({ path: '/operation_list' })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ .el-form-item {
    margin-bottom: 5px;
  }

  .app-container /deep/ textarea {
    height: 280px;
  }
</style>
