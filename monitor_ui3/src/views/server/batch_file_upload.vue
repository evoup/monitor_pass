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
          <el-col :span="15">
            <div>
              <!--原先编辑的地方-->
              <el-upload
                ref="upload"
                :show-file-list="true"
                :auto-upload="false"
                :http-request="uploadImg"
                action="string">
                <el-button slot="trigger" size="small" type="primary">点击上传</el-button>
                <div slot="tip" class="el-upload__tip">上传单个不超过500M的文件</div>
              </el-upload>
              <el-progress :percentage="progressPercent"/>
            </div>
          </el-col>
          <el-col :span="9">
            <el-input v-model="send_user_input" placeholder="请输入分发的系统用户名" class="send_user"/>
            <el-input v-model="send_dir_input" placeholder="请输入要分发到的系统路径" class="send_dir"/>
            <el-button type="success" class="send_button" @click="send_files">分发到服务器</el-button>
          </el-col>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { server_group_list, server_list } from '../../api/server'
import { get_command_result } from '../../api/batch_operation'
import { getToken } from '../../utils/auth'
import * as axios from 'axios'

export default {
  name: 'BatchFileUpload',
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
      send_dir_input: null,
      fileList: [],
      header: this.getHeader(),
      progressPercent: 0
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
      })
    },
    send_files() {
      this.$refs.upload.submit()
      // var command = this.$refs.editAreaShellComp.code
      // batch_send_commands(this.$refs.tree1.getCheckedNodes(), this.send_user_input, command).then(response => {
      //   var tasks = response.data.items
      //   for (var i in tasks) {
      //     this.wait_command_finish(tasks[i])
      //   }
      // })
    },
    // 轮训任务结果
    wait_command_finish(task_id) {
      get_command_result({ task_id: task_id }).then(response => {
        if (response.data.item != null) {
          this.resultModel = this.resultModel + '\n' + response.data.item.name + '执行命令完成，结果如下：\n' + response.data.item.out
        } else {
          setTimeout(() => {
            this.wait_command_finish(task_id)
          }, 2000)
        }
      })
    },
    // 执行结果滚动条滚动到底部
    scrollToBottom: function() {
      this.$nextTick(() => {
        var div = document.getElementById('textarea0')
        div.scrollTop = div.scrollHeight
      })
    },
    handleRemove(file, fileList) {
      console.log(file, fileList)
    },
    handlePreview(file) {
      console.log(file)
    },
    getHeader() {
      return {
        'Authorization': 'JWT ' + getToken(),
        'Content-Disposition': 'form-data; name="file"; filename="filename"',
        'Content-Type': 'multipart/form-data'
      }
    },
    // 自定义上传方法，因为django默认的uploadfileParser是二进制的
    uploadImg(item) {
      console.log(item.file)
      const formData = new FormData()
      formData.append('file', item.file)
      formData.append('hosts', JSON.stringify(this.$refs.tree1.getCheckedNodes()))
      formData.append('username', this.send_user_input)
      formData.append('send_dir', this.send_dir_input)
      axios({
        url: process.env.BASE_API + '/batch_operation/upload',
        method: 'post',
        data: formData,
        headers: this.getHeader(),
        onUploadProgress: progressEvent => {
          // progressEvent.loaded:已上传文件大小
          // progressEvent.total:被上传文件的总大小
          this.progressPercent = Math.floor(progressEvent.loaded / progressEvent.total * 100)
        }
      })
        .then((data) => {
          console.log(data)
        })
        .catch((err) => {
          console.log(err, 'error')
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
  .send_dir {
    width:250px;
    margin-top:20px;
    margin-left:20px;
  }
  .send_user {
    width:250px;
    margin-top:20px;
    margin-left:20px;
  }
  .send_button {
    width:250px;
    margin-top:20px;
    margin-left:20px;
  }
</style>
