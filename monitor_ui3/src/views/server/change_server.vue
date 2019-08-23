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
      <el-form-item label="SSH">
        <el-col :span="8">
          <el-input v-model="form.ssh" placeholder="请输入ip:port"/>
        </el-col>
      </el-form-item>
      <el-form-item label="SNMP">
        <el-col :span="8">
          <el-input v-model="form.snmp" placeholder="请输入ip:port"/>
        </el-col>
      </el-form-item>
      <el-form-item label="JMX">
        <el-col :span="8">
          <el-input v-model="form.jmx" placeholder="请输入ip:port"/>
        </el-col>
      </el-form-item>
      <el-form-item label="服务器组">
        <el-col :span="14">
          <el-select
            v-model="serverGroupSelectModel"
            multiple
            placeholder="请选择服务器组（可选0个或多个）"
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
            placeholder="请选择模板（可选0个或多个）"
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
      <el-form-item label="数据收集器">
        <el-col :span="14">
          <el-select
            v-model="dataCollectorSelectModel"
            placeholder="请选择数据收集器"
            style="width: 40%"
          >
            <el-option
              v-for="item in form.dataCollectors"
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
            placeholder="请输入或选择机房（可选）"
            style="width: 40%"
            filterable
            allow-create
          >
            <el-option
              v-for="item in form.idcs"
              :key="item.id"
              :label="item.name"
              :aria-selected="true"
              :value="item.id"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="自动收集资产">
        <el-switch
          v-model="form.auto_asset"
        />
      </el-form-item>
      <el-form-item label="是否监控">
        <el-switch
          v-model="form.monitoring"
        />
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            changeServer(form.name, form.client, form.ssh, form.jmx, form.snmp, dataCollectorSelectModel, idcSelectModel, serverGroupSelectModel, templateSelectModel, form.auto_asset)
          "
        >更新
        </el-button
        >
        <el-button @click="jumpServerList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { change_server, read_server, server_group_list } from '../../api/server'
import { template_list } from '../../api/template'
import { idc_list } from '../../api/idc'
import { data_collector_list } from '../../api/data_collector'

export default {
  data() {
    return {
      form: {
        name: '',
        client: '',
        ssh: '',
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
        dataCollectors: [],
        idcs: [],
        auto_asset: true,
        monitoring: true
      },
      serverGroupSelectModel: null,
      templateSelectModel: null,
      dataCollectorSelectModel: null,
      idcSelectModel: null
    }
  },
  created() {
    this.fetchServerGroupListData()
    this.fetchTemplateListData()
    this.fetchIdcListData()
    this.fetchDataCollectorListData()
    this.fetchData(this.$route.query.id)
  },
  methods: {
    fetchData(id) {
      read_server({ id: id }).then(response => {
        this.form.name = response.data.item.name
        this.form.client = response.data.item.agent_address
        this.form.ssh = response.data.item.ssh_address
        this.form.snmp = response.data.item.snmp_address
        this.form.jmx = response.data.item.jmx_address
        // 所属的服务器组
        const serverGroupIds = response.data.item.server_groups
        const a = []
        serverGroupIds.forEach(function(e) {
          a.push(e)
        })
        this.serverGroupSelectModel = a
        // 数据收集器
        this.dataCollectorSelectModel = response.data.item.data_collector
        // 机房
        this.idcSelectModel = response.data.item.asset.idc.id
      })
    },
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
    fetchDataCollectorListData() {
      data_collector_list().then(response => {
        this.form.dataCollectors = response.data.items
      })
    },
    fetchIdcListData() {
      idc_list().then(response => {
        this.form.idcs = response.data.items
      })
    },
    changeServer(a, b, c, d, e, f, g, h, i, j) {
      change_server(this.$route.query.id, a, b, c, d, e, f, g, h, i, j).then(response => {
      }).catch(e => {
        console.log(e)
      })
    },
    jumpServerList() {
      this.$router.push({ path: '/server_list' })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ .el-form-item {
    margin-bottom: 8px;
  }
</style>
