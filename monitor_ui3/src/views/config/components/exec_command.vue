<template>
  <div class="param-form">
    <el-form ref="operationForm" :model="operationForm" label-width="120px">
      <el-form-item label="指定的轮次：">
        <el-col :span="6">
          <el-input v-model="operationForm.step" placeholder=""/>
        </el-col>
        <span style="font-size: 10px;color: dimgrey;padding-left:10px;">（a-b代表从第a次到第b次，b为0代表无限次）</span>
      </el-form-item>
      <el-form-item label="发送间隔：">
        <el-col :span="6">
          <el-input v-model="operationForm.send_interval" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="执行目标：">
        <el-col :span="6"/>
        <el-col :span="24">
          <el-radio v-model="operationForm.exec_target" label="1">当前服务器</el-radio>
          <el-radio v-model="operationForm.exec_target" label="2">指定服务器</el-radio>
          <el-radio v-model="operationForm.exec_target" label="3">指定服务器组</el-radio>
          <el-select
            v-if="operationForm.exec_target==='2'"
            v-model="select_value"
            :remote-method="remoteMethod"
            :loading="loading"
            multiple
            filterable
            remote
            reserve-keyword
            placeholder="请输入关键词">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value"/>
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="ssh用户名：">
        <el-col :span="6">
          <el-input v-model="operationForm.ssh_user" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="ssh密码：">
        <el-col :span="6">
          <el-input v-model="operationForm.ssh_user" placeholder=""/>
        </el-col>
      </el-form-item>
      <el-form-item label="命令：">
        <el-col :span="17">
          <el-input
            v-model="operationForm.command"
            class="note"
            placeholder="请输入命令"
            type="textarea"
          />
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
class OperationForm {
  constructor() {
    this.send_interval = 3600
    this.step = '1-1'
    this.ssh_user = ''
    this.command = ''
    this.exec_target = '1'
  }
}
export default {
  name: 'ExecCommand',
  data() {
    return {
      operationForm: new OperationForm(),
      options: [],
      select_value: [],
      loading: false,
      states: [{ id: 1, name: 'Alabama' }, { id: 2, name: 'Kakaxi' }]
    }
  },
  mounted() {
    this.list = this.states.map(item => {
      return { value: item.id, label: item.name }
    })
  },
  methods: {
    remoteMethod(query) {
      if (query !== '') {
        this.loading = true
        setTimeout(() => {
          this.loading = false
          this.options = this.list.filter(item => {
            return item.label.toLowerCase()
              .indexOf(query.toLowerCase()) > -1
          })
          console.log(this.select_value)
        }, 200)
      } else {
        this.options = []
      }
    }
  }
}
</script>

<style scoped>
  .param-form /deep/ .el-form-item {
    margin-bottom: 5px;
  }

  .param-form /deep/ textarea {
    height: 150px;
  }
</style>
