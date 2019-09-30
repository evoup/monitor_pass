<template>
  <div class="app-container">
    <el-row>
      <el-col :span="6"><div>
        <el-tree ref="tree1" :data="tree_data" show-checkbox @check-change="handleClick" style="background: transparent"/>
      </div></el-col>
      <el-col :span="18"><div>
        <el-input
          v-model="resultModel"
          placeholder=""
          type="textarea" id="textarea0"/>
      </div></el-col>
    </el-row>
    <!--下方输入框和按钮-->
    <el-row>
      <el-col :span="6"><div class="grid-content"></div></el-col>
      <el-col :span="18">
        <el-input
          v-model="commandModel"
          placeholder=""
          type="textarea" id="textarea1"/>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { server_group_list, server_list } from '../../api/server'

export default {
  name: 'BatchCommandExec',
  data() {
    return {
      tree_data: [],
      pageHelp: {
        page: 1,
        size: 7,
        order: 'asc'
      },
      sortHelp: {
        prop: '',
        order: ''
      },
      resultModel: '',
      commandModel: 'ls -la'
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    buildTree(data0, data) {
      this.tree_data = []
      for (const i in data0) {
        const server_group_id = 'server_group|' + data0[i].id
        const label = data0[i].name
        const member_servers = []
        for (const j in data) {
          const node_label = data[j].hostname
          member_servers.push({ id: 'server|' + data[j].id, label: node_label })
        }
        const server_group_member = { id: server_group_id, label: label, children: member_servers }
        this.tree_data.push(server_group_member)
      }
      console.log(this.tree_data)
    },
    handleClick(data) {
      // const checkedNodes = this.$refs.tree1.getCheckedNodes()
      // const user_group_user_ids = new Set([])
      // for (const i in checkedNodes) {
      //   user_group_user_ids.add(checkedNodes[i].id)
      // }
      // this.operationForm.userGroupSelectModel = user_group_user_ids
    },
    fetchData() {
      server_group_list().then(response => {
        const server_groups = response.data.items
        server_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
          this.buildTree(server_groups, response.data.items)
        })
      })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ #textarea0 {
    height: 380px;
    background: #1f2d3d;
    color: chartreuse;
  }
  .app-container /deep/ #textarea1 {
    height: 180px;
    background: #d3dce6;
    color: #1f2d3d;
  }
  .grid-content {
    border-radius: 4px;
    min-height: 36px;
  }

</style>
