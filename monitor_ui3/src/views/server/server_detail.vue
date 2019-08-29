<template>
  <div class="app-container">
    <el-tabs type="border-card" @tab-click="genGrafana">
      <!-- tab-pane1 -->
      <el-tab-pane label="明细状态">
        <div id="status">
          <ul class="list_1">
            <li>
              <el-row>
                <el-col :span="20">
                  <strong>主机名：</strong>
                  <span>
                    {{ form.name }}
                  </span>
                </el-col>
                <el-col :span="2">
                  <el-button type="primary" plain @click.native.prevent="jumpChangeServer">
                    配置
                  </el-button>
                </el-col>
                <el-col :span="2">
                  <el-button type="danger" plain @click.native.prevent="deleteServer">
                    删除
                  </el-button>
                </el-col>
              </el-row>
            </li>
            <li>
              <strong>所属服务器组：</strong>
              <span>
                {{ form.server_groups }}
              </span>
            </li>
            <li>
              <strong>IP地址：</strong>
              <span>
                {{ form.ip }}
              </span>
            </li>

            <li>
              <strong>运行时间：</strong>
              <span>
                22.53d 22.55h m s
              </span>
            </li>

            <li>
              <strong>上次上传：</strong>
              <span>
                2019-08-19 06:56:22
              </span>
            </li>

            <li>
              <strong>上次更新：</strong>
              <span>
                2019-08-19 06:56:08
              </span>
            </li>

            <li>
              <strong>客户端版本号：</strong>
              <span>
                版本过低,需更新
              </span>
            </li>

          </ul>
          <div id="detail">
            <ul class="list_1">

              <li>
                <strong>Load Average：</strong>
                <span>
                  22.52
                </span>
              </li>

              <li>
                <strong>TCP连接数：</strong>
                <span>
                  15
                </span>
              </li>

              <li>
                <strong>cpu：</strong>
                <span>
                  usage:93.89% use:16.9 nice:0.0 system:76.9 interrupt:0 idle:6.1
                </span>
              </li>

              <li>
                <strong>内存：</strong>
                <span>
                  active:2.86Gb inactive:0Bytes wired:0Bytes cache:0Bytes buf:0Bytes free:900.12Mb
                </span>
              </li>

              <li>
                <strong>SWAP：</strong>
                <span>
                  total:3.87Gb used:8.52Mb free:3.87Gb inuse: 1.32Gb
                </span>
              </li>

              <li>
                <strong>磁盘：</strong>
                <span>
                  disk:/ 29% disk:/boot 7% disk:/dev/shm 0%
                </span>
              </li>

              <li>
                <strong>文件系统Inode：</strong>
                <span>
                  inode:/ 52% inode:/boot 1% inode:/dev/shm 1%
                </span>
              </li>

              <li>
                <strong>进程：</strong>
                <span>
                  sum:0 starting:0 running:4 sleeping:198 stopped:0 zombie:41 waiting:0 lock:0
                </span>
              </li>

              <li>
                <strong>网络接口：</strong>
                <span>
                  -
                </span>
              </li>

            </ul>
          </div>
        </div>
      </el-tab-pane>
      <el-tab-pane label="实时监控图表">
        <div v-for="(v,_) in all_diagrams" :key="shit_key" v-html="v"/>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import { delete_server, read_server, server_group_list } from '../../api/server'
import { server_diagram_list } from '../../api/diagram'

export default {
  name: 'ServerDetail',
  data() {
    return {
      form: {
        name: '',
        ip: '',
        server_groups: null
      },
      all_diagrams: [],
      shit_key: 0
    }
  },
  created() {
    this.fetchData(this.$route.query.id)
  },
  methods: {
    fetchData(id) {
      var server_groups = []
      server_group_list().then(response => {
        server_groups = response.data.items
      })
      read_server({ id: id }).then(response => {
        this.form.name = response.data.item.name
        this.form.ip = response.data.item.ip
        var grps = []
        for (var grp in response.data.item.server_groups) {
          if (server_groups[grp] !== null && server_groups[grp].hasOwnProperty('name')) {
            grps.push(server_groups[grp].name)
          }
        }
        this.form.server_groups = grps.join()
      })
      // 加载图表
      server_diagram_list({ id: this.$route.query.id }).then(response => {
        const ts = Date.parse(new Date())
        for (const i in response.data.items) {
          const diagram_name = response.data.items[i].dname
          const width = response.data.items[i].width
          const height = response.data.items[i].height
          /* eslint-disable */
          this.all_diagrams.push(`<!--${diagram_name}--><iframe src="http://${document.domain}/${response.data.items[i].url}${ts}" width="${width}" height="${height}" frameborder="0" />`)
        }
      })
    },
    genGrafana() {
      this.y = this.x + Date.parse(new Date())
      this.shit_key = this.shit_key + 1
    },
    // 删除服务器
    deleteServer() {
      delete_server({ id: this.$route.query.id }).then(response => {
        this.jumpServerList()
      })
    },
    jumpServerList() {
      this.$router.push({ path: '/server_list' })
    },
    jumpChangeServer() {
      this.$router.push({ path: '/change_server?id=' + this.$route.query.id })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ .list_1 li {
    list-style-type: none;
    font-size: 14px;
    line-height: 30px;
    color: #606266;
  }
</style>
