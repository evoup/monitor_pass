<template>
  <div class="app-container">
    <el-row>
      <el-col :span="6"><div class="grid-content bg-purple">
        <el-tree ref="tree1" :data="tree_data" show-checkbox @check-change="handleClick"/>
      </div></el-col>
      <el-col :span="18"><div class="grid-content bg-purple">
        <el-input
          v-model="commandModel"
          placeholder=""
          type="textarea"/>
      </div></el-col>
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
      commandModel: 'ls -la'
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    buildTree(data) {
      this.tree_data = []
      for (const i in data) {
        console.log(data[i])
        // const user_group_id = 'user_group|' + data[i].id
        // const label = data[i].name
        // const members = []
        // for (const j in data[i].member_list) {
        //   members.push({ id: 'user|' + data[i].member_list[j].id, label: data[i].member_list[j].first_name })
        // }
        // const usergroup_member = { id: user_group_id, label: label, children: members }
        // this.tree_data.push(usergroup_member)
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
        console.log(response.data.items)
      })
      server_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.buildTree(response.data.items)
      })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ textarea {
    height: 380px;
    background: #1f2d3d;
    color: chartreuse;
  }
</style>
