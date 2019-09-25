<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="操作名称：">
        <el-col :span="8">
          <el-input v-model="form.name" :disabled="true" placeholder="操作名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="操作类型：">
        <el-col :span="10">
          <el-radio v-model="runTypeModel" label="1" @change="changeData('send_notice')">发送消息</el-radio>
          <el-radio v-model="runTypeModel" label="2" @change="changeData('exec_command')">执行命令</el-radio>
        </el-col>
      </el-form-item>
      <el-col :span="10">
        <component ref="operationFormComp" :is="componentFile" />
      </el-col>
      <el-col :span="24">
        <el-form-item>
          <el-button
            type="primary"
            @click="
              addOperationItem()
            "
          >创建
          </el-button
          >
          <el-button @click="jumpOperationList">取消</el-button>
        </el-form-item>
      </el-col>
    </el-form>
  </div>
</template>

<script>
import { add_operation_item, read_operation } from '../../api/operation'

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
    this.fetchData()
  },
  methods: {
    changeData(d) {
      this.$store.state.operationTypeComponentName = d
    },
    fetchData() {
      read_operation({ id: this.$route.query.id }).then(response => {
        this.form.name = response.data.item.name
      })
    },
    addOperationItem() {
      add_operation_item(this.runTypeModel, this.$refs.operationFormComp.$refs.operationForm.model)
    },
    jumpOperationList() {
      this.$router.push({ path: '/operation_list' })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ textarea {
    height:120px;
  }
</style>
