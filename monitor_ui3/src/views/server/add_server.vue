<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="主机名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入hostname" />
        </el-col>
      </el-form-item>
      <el-form-item label="监控代理">
        <el-col :span="8">
          <el-input v-model="form.client" placeholder="请输入ip:port" />
        </el-col>
      </el-form-item>
      <el-form-item label="SNMP">
        <el-col :span="14">
          <el-input v-model="form.snmp" />
        </el-col>
      </el-form-item>
      <el-form-item label="JMX">
        <el-col :span="14">
          <el-input v-model="form.jmx" />
        </el-col>
      </el-form-item>
      <el-form-item label="服务器组">
        <el-col :span="14">
          <el-select
            v-model="serverGroupSelectModel"
            multiple
            placeholder="请选择服务器组（可选）"
            style="width: 80%"
          >
            <el-option
              v-for="item in form.serverGroups"
              :key="item.id"
              :label="item.name"
              :aria-selected="true"
              :value="item.id"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="模板">
        <el-col :span="14">
          <el-select
            v-model="templateSelectModel"
            multiple
            placeholder="请选择模板（可选）"
            style="width: 80%"
          >
            <el-option
              v-for="item in form.templates"
              :key="item.id"
              :label="item.name"
              :aria-selected="true"
              :value="item.id"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="所在机房">
        <el-col :span="14">
          <el-select
            v-model="idcSelectModel"
            placeholder="请选择机房（可选）"
            style="width: 80%"
            filterable
            allow-create
          >
            <el-option
              v-for="item in form.idcs"
              :key="item.id"
              :label="item.name"
              :aria-selected="true"
              :value="item.name"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            addServer(form.name, form.client, form.jmx, form.snmp, idcSelectModel, serverGroupSelectModel,
                      templateSelectModel)
          "
        >创建</el-button
        >
        <el-button @click="jumpServerList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_server, server_group_list } from '../../api/server'
import { template_list } from '../../api/template'
import { idc_list } from '../../api/idc'
export default {
  data() {
    return {
      form: {
        client: '',
        name: '',
        jmx: '',
        snmp: '',
        idc: '',
        region: '',
        date1: '',
        date2: '',
        delivery: false,
        type: [],
        resource: '',
        desc: '',
        templates: [],
        serverGroups: [],
        idcs: []
      },
      serverGroupSelectModel: null,
      templateSelectModel: null,
      idcSelectModel: null
    }
  },
  created() {
    this.fetchServerGroupListData()
    this.fetchTemplateListData()
    this.fetchIdcListData()
  },
  methods: {
    // 获取所有模板列表
    fetchTemplateListData() {
      template_list().then(response => {
        this.form.templates = response.data.items
      })
    },
    fetchServerGroupListData() {
      server_group_list().then(response => {
        this.form.serverGroups = response.data.items
      })
    },
    fetchIdcListData() {
      idc_list().then(response => {
        this.form.idcs = response.data.items
      })
    },
    addServer(a, b, c, d, e, f, g) {
      add_server(a, b, c, d, e, f, g)
    },
    jumpServerList() {
      this.$router.push({ path: '/server_list' })
    }
  }
}
</script>

<style scoped></style>
