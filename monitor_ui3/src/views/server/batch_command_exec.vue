<template>
  <div class="app-container">
    <el-row>
      <el-col :span="6"><div>
        <el-tree ref="tree1" :data="tree_data" show-checkbox style="background: transparent" @check-change="handleClick"/>
      </div></el-col>
      <el-col :span="18"><div>
        <el-input
          id="textarea0"
          v-model="resultModel"
          placeholder=""
          type="textarea"/>
      </div></el-col>
    </el-row>
    <!--下方输入框和按钮-->
    <el-row style="margin-top:20px">
      <el-col :span="6">
        <div class="grid-content"/>
      </el-col>
      <el-col :span="18">
        <el-row>
          <el-col :span="20">
            <div>
              <component ref="editAreaShellComp" :is="componentFile" />
            </div>
          </el-col>
          <el-col :span="4">
            <el-input v-model="send_user_input" placeholder="请输入执行的系统用户名" class="send_user"></el-input>
            <el-button type="primary" class="send_button">执行</el-button>
          </el-col>
        </el-row>
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
      commandModel: 'ls -la',
      send_user_input: ''
    }
  },
  computed: {
    componentFile() {
      return () => import(`./components/edit_area_shell.vue`)
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
  .grid-content {
    border-radius: 4px;
    min-height: 36px;
  }
  .send_user {
    width:170px;
    margin-top:20px;
    margin-left:20px;
  }
  .send_button {
    width:170px;
    margin-top:20px;
    margin-left:20px;
  }

</style>
