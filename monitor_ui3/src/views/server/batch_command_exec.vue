<template>
  <div class="app-container">
    <el-row>
      <el-col :span="6"><div>
        <el-tree ref="tree1" :data="tree_data" show-checkbox style="background: transparent"/>
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
          <el-col :span="18">
            <div>
              <component ref="editAreaShellComp" :is="componentFile" />
            </div>
          </el-col>
          <el-col :span="6">
            <el-input v-model="send_user_input" placeholder="请输入执行的系统用户名" class="send_user"/>
            <el-button type="primary" class="send_button" @click="send_command">执行</el-button>
          </el-col>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { server_group_list, server_list } from '../../api/server'
import { batch_send_commands, get_command_result } from '../../api/batch_operation'

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
      commandModel: null,
      send_user_input: null,
      totalTasks: []
    }
  },
  computed: {
    componentFile() {
      return () => import(`./components/edit_area_shell.vue`)
    }
  },
  watch: {
    'resultModel': 'scrollToBottom'
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
    fetchData() {
      server_group_list().then(response => {
        const server_groups = response.data.items
        server_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
          this.buildTree(server_groups, response.data.items)
        })
      }).catch(e => {
        console.log(e)
      })
    },
    send_command() {
      var command = this.$refs.editAreaShellComp.code
      batch_send_commands(this.$refs.tree1.getCheckedNodes(), this.send_user_input, command).then(response => {
        var tasks = response.data.items
        for (var i in tasks) {
          this.totalTasks.push(tasks[i])
          this.wait_command_finish(tasks[i])
        }
      }).catch(e => {
        console.log(e)
      })
    },
    // 轮训任务结果
    wait_command_finish(task_id) {
      get_command_result({ task_id: task_id }).then(response => {
        if (response.data.item != null) {
          this.resultModel = this.resultModel + '\n' + response.data.item.name + '执行命令完成，结果如下：\n' + this.decodeBase64Content(response.data.item.out)
          // 从总任务中减去当前任务
          var i = this.totalTasks.indexOf(task_id)
          if (i !== -1) {
            this.totalTasks.splice(i, 1)
          }
          if (this.totalTasks.length === 0) {
            this.resultModel = this.resultModel + '\n' + '----------------------\n所有任务完成!'
          }
        } else {
          setTimeout(() => {
            this.wait_command_finish(task_id)
          }, 2000)
        }
      }).catch(e => {
        console.log(e)
      })
    },
    // 执行结果滚动条滚动到底部
    scrollToBottom: function() {
      this.$nextTick(() => {
        var div = document.getElementById('textarea0')
        div.scrollTop = div.scrollHeight
      })
    },
    decodeBase64Content: function(base64Content) {
      let commonContent = base64Content.replace(/\s/g, '+');
      commonContent = Buffer.from(commonContent, 'base64').toString();
      return commonContent;
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
