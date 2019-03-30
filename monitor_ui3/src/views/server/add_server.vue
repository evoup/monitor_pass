<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="主机名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入hostname"/>
        </el-col>
      </el-form-item>
      <el-form-item label="监控代理">
        <el-col :span="8">
          <el-input v-model="form.client" placeholder="请输入ip:port"/>
        </el-col>
      </el-form-item>
      <el-form-item label="SNMP">
        <el-col :span="14">
          <el-input v-model="form.snmp"/>
        </el-col>
      </el-form-item>
      <el-form-item label="JMX">
        <el-col :span="14">
          <el-input v-model="form.jmx"/>
        </el-col>
      </el-form-item>
      <el-form-item label="模板">
        <el-col :span="14">
          <el-input v-model="form.template" type="textarea" />
        </el-col>
      </el-form-item>
      <el-form-item label="所在机房">
        <el-col :span="14">
          <el-input v-model="form.idc" type="textarea" />
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addServer(form.name,form.client, form.jmx, form.snmp, form.idc)">创建</el-button>
        <el-button @click="onCancel">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_server } from '@/api/server'
export default {
  data() {
    return {
      form: {
        client: '',
        name: '',
        jmx: '',
        snmp: '',
        region: '',
        date1: '',
        date2: '',
        delivery: false,
        type: [],
        resource: '',
        desc: ''
      }
    }
  },
  methods: {
    onSubmit() {
      add_server('a', 'b', 'c', 'd', 'e')
      this.$message('submit!')
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning'
      })
    },
    addServer(a, b, c, d, e) {
      add_server(a, b, c, d, e)
      this.$message('服务器已添加!')
    }
  }
}
</script>

<style scoped>

</style>
