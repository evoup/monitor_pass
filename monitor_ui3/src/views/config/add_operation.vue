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
      <el-form-item label="启用：">
        <el-col>
          <el-switch
            v-model="form.status"
          />
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            addOperation(form.name,form.subject,form.message, triggerSelectModel, form.status)
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
import { add_operation } from '../../api/operation'

export default {
  name: 'AddOperation',
  data() {
    return {
      form: {
        name: '',
        subject: '{TRIGGER.STATUS}: {TRIGGER.NAME}',
        message: '触发器: {TRIGGER.NAME}\n' +
          '触发器状态: {TRIGGER.STATUS}\n' +
          '严重等级: {TRIGGER.LEVEL}\n' +
          // 'Trigger URL: {TRIGGER.URL}\n' +
          '\n' +
          '监控项数值:\n' +
          '\n' +
          '1. {MONITOR_ITEM.NAME1} ({HOST.NAME1}:{MONITOR_ITEM.KEY1}): {MONITOR_ITEM.VALUE1}\n' +
          '2. {MONITOR_ITEM.NAME2} ({HOST.NAME2}:{MONITOR_ITEM.KEY2}): {MONITOR_ITEM.VALUE2}\n' +
          '3. {MONITOR_ITEM.NAME3} ({HOST.NAME3}:{MONITOR_ITEM.KEY3}): {MONITOR_ITEM.VALUE3}\n' +
          '\n',
        // 'Original event ID: {EVENT.ID}',
        status: true
      },
      templateListData: [],
      templateSelectModel: null,
      triggerListData: [],
      triggerSelectModel: null
    }
  },
  created() {
    this.fetchTemplateListData()
  },
  methods: {
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
    addOperation(a, b, c, d, e) {
      add_operation(a, b, c, d, e).then(response => {
        this.openConfirm(response.id)
      }).catch(e => {
        console.log(e)
      })
    },
    jumpOperationList() {
      this.$router.push({ path: '/operation_list' })
    },
    openConfirm(i) {
      this.$confirm('将在操作下创建操作项, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'info'
      }).then(() => {
        this.$router.push({ path: '/add_operation_item?id=' + i })
      }).catch(() => {
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
    height: 280px;
  }
</style>
