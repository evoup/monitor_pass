<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="230px">
      <el-form-item label="Grafana的api key：">
        <el-col :span="18">
          <el-input v-model="form.api_key" placeholder="请输入api key"/>
        </el-col>
      </el-form-item>
      <el-form-item label="服务端故障时告警：">
        <el-col :span="8">
          <el-switch
            v-model="form.switchValue"
            active-value="1"
            inactive-value="0"
          />
        </el-col>
      </el-form-item>
      <el-form-item label="批量命令执行中的命令过滤规则：">
        <el-col :span="8">
          <el-input
            v-model="form.filter_command"
            placeholder=""
            type="textarea"
          />
        </el-col>
      </el-form-item>
      <el-form-item label="ssh私钥路径（批量命令执行和批量文件分发中用到）：">
        <el-col :span="8">
          <el-input
            v-model="form.private_key_dir"
            placeholder=""
          />
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="changeGeneralConfig(form.api_key, form.switchValue)">更新</el-button>
        <el-button @click="jumpTemplateList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { change_general_config, read_general_config } from '../../api/general_config'

export default {
  name: 'GeneralConfig',
  data() {
    return {
      form: {
        api_key: null,
        switchValue: null,
        filter_command: 'rm -rf\nreboot\npoweroff\nsu',
        private_key_dir: null
      }
    }
  },
  created() {
    this.readGeneralConfig()
  },
  methods: {
    readGeneralConfig() {
      read_general_config().then(response => {
        this.form.api_key = response.data.item.grafana_api_key
        this.form.switchValue = response.data.item.send_warn ? '1' : '2'
      })
    },
    changeGeneralConfig(a, b) {
      change_general_config(a, b)
    },
    jumpTemplateList() {
      this.$router.push({ path: '/template_list' })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ textarea {
    height: 100px;
  }
</style>
