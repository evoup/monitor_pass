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
        <el-select
          v-model="dateModel"
          placeholder="请选择时间"
          style="margin-bottom:10px;float: right"
          @change="loadDiagram"
        >
          <el-option
            v-for="item in form.dateList"
            :key="item.id"
            :label="item.name"
            :aria-selected="true"
            :value="item.id"
          />
        </el-select>
        <!--如果不用shit_key这个技巧，就不能正常刷新-->
        <div v-if="dateModel==1" :key="shit_key + 1" v-html="all_diagrams_1"/>
        <div v-if="dateModel==2" :key="shit_key + 1" v-html="all_diagrams_2"/>
        <div v-if="dateModel==3" :key="shit_key + 1" v-html="all_diagrams_3"/>
        <div v-if="dateModel==4" :key="shit_key + 1" v-html="all_diagrams_4"/>
        <div v-if="dateModel==5" :key="shit_key + 1" v-html="all_diagrams_5"/>
        <div v-if="dateModel==6" :key="shit_key + 1" v-html="all_diagrams_6"/>
        <div v-if="dateModel==7" :key="shit_key + 1" v-html="all_diagrams_7"/>
        <div v-if="dateModel==8" :key="shit_key + 1" v-html="all_diagrams_8"/>
        <div v-if="dateModel==9" :key="shit_key + 1" v-html="all_diagrams_9"/>
        <div v-if="dateModel==10" :key="shit_key + 1" v-html="all_diagrams_10"/>
        <div v-if="dateModel==11" :key="shit_key + 1" v-html="all_diagrams_11"/>
        <div v-if="dateModel==12" :key="shit_key + 1" v-html="all_diagrams_12"/>
        <div v-if="dateModel==13" :key="shit_key + 1" v-html="all_diagrams_13"/>
        <div v-if="dateModel==14" :key="shit_key + 1" v-html="all_diagrams_14"/>
        <div v-if="dateModel==15" :key="shit_key + 1" v-html="all_diagrams_15"/>
        <div v-if="dateModel==16" :key="shit_key + 1" v-html="all_diagrams_16"/>
        <div v-if="dateModel==17" :key="shit_key + 1" v-html="all_diagrams_17"/>
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
        server_groups: null,
        dateList: [
          { id: 1, name: '最近5分钟' },
          { id: 2, name: '最近15分钟' },
          { id: 3, name: '最近30分钟' },
          { id: 4, name: '最近1小时' },
          { id: 5, name: '最近3小时' },
          { id: 6, name: '最近6小时' },
          { id: 7, name: '最近12小时' },
          { id: 8, name: '最近24小时' },
          { id: 9, name: '最近2天' },
          { id: 10, name: '最近7天' },
          { id: 11, name: '最近30天' },
          { id: 12, name: '最近90天' },
          { id: 13, name: '最近半年' },
          { id: 14, name: '最近1年' },
          { id: 15, name: '最近2年' },
          { id: 16, name: '最近5年' },
          { id: 17, name: '去年' }
        ]
      },
      all_diagrams_1: '',
      all_diagrams_2: '',
      all_diagrams_3: '',
      all_diagrams_4: '',
      all_diagrams_5: '',
      all_diagrams_6: '',
      all_diagrams_7: '',
      all_diagrams_8: '',
      all_diagrams_9: '',
      all_diagrams_10: '',
      all_diagrams_11: '',
      all_diagrams_12: '',
      all_diagrams_13: '',
      all_diagrams_14: '',
      all_diagrams_15: '',
      all_diagrams_16: '',
      all_diagrams_17: '',
      shit_key: 0,
      // 默认最近6小时
      dateModel: 6
    }
  },
  created() {
    this.fetchData(this.$route.query.id)
  },
  methods: {
    loadDiagram() {
      switch (this.dateModel) {
        case (1):
          server_diagram_list({ id: this.$route.query.id, now: 'now-5m' }).then(response => {
            this.all_diagrams_1 = response.data.item
          })
          break
        case (2):
          server_diagram_list({ id: this.$route.query.id, now: 'now-15m' }).then(response => {
            this.all_diagrams_2 = response.data.item
          })
          break
        case (3):
          server_diagram_list({ id: this.$route.query.id, now: 'now-30m' }).then(response => {
            this.all_diagrams_3 = response.data.item
          })
          break
        case (4):
          server_diagram_list({ id: this.$route.query.id, now: 'now-1h' }).then(response => {
            this.all_diagrams_4 = response.data.item
          })
          break
        case (5):
          server_diagram_list({ id: this.$route.query.id, now: 'now-3h' }).then(response => {
            this.all_diagrams_5 = response.data.item
          })
          break
        case (6):
          server_diagram_list({ id: this.$route.query.id, now: 'now-6h' }).then(response => {
            this.all_diagrams_6 = response.data.item
          })
          break
        case (7):
          server_diagram_list({ id: this.$route.query.id, now: 'now-12h' }).then(response => {
            this.all_diagrams_7 = response.data.item
          })
          break
        case (8):
          server_diagram_list({ id: this.$route.query.id, now: 'now-24h' }).then(response => {
            this.all_diagrams_8 = response.data.item
          })
          break
        case (9):
          server_diagram_list({ id: this.$route.query.id, now: 'now-2d' }).then(response => {
            this.all_diagrams_9 = response.data.item
          })
          break
        case (10):
          server_diagram_list({ id: this.$route.query.id, now: 'now-7d' }).then(response => {
            this.all_diagrams_10 = response.data.item
          })
          break
        case (11):
          server_diagram_list({ id: this.$route.query.id, now: 'now-30d' }).then(response => {
            this.all_diagrams_11 = response.data.item
          })
          break
        case (12):
          server_diagram_list({ id: this.$route.query.id, now: 'now-90d' }).then(response => {
            this.all_diagrams_12 = response.data.item
          })
          break
        case (13):
          server_diagram_list({ id: this.$route.query.id, now: 'now-6M' }).then(response => {
            this.all_diagrams_13 = response.data.item
          })
          break
        case (14):
          server_diagram_list({ id: this.$route.query.id, now: 'now-1y' }).then(response => {
            this.all_diagrams_14 = response.data.item
          })
          break
        case (15):
          server_diagram_list({ id: this.$route.query.id, now: 'now-2y' }).then(response => {
            this.all_diagrams_15 = response.data.item
          })
          break
        case (16):
          server_diagram_list({ id: this.$route.query.id, now: 'now-5y' }).then(response => {
            this.all_diagrams_16 = response.data.item
          })
          break
        case (17):
          server_diagram_list({ id: this.$route.query.id, now: 'now-1d' }).then(response => {
            this.all_diagrams_17 = response.data.item
          })
          break
      }
    },
    fetchData(id) {
      var server_groups = []
      server_group_list().then(response => {
        server_groups = response.data.items
        read_server({ id: id }).then(response => {
          this.form.name = response.data.item.name
          this.form.ip = response.data.item.ip
          var grps = []
          for (var grp in response.data.item.server_groups) {
            if (server_groups[grp] !== null && server_groups[grp].hasOwnProperty('name')) {
              grps.push(server_groups[grp].name)
            }
          }
          this.form.server_groups = grps.join(', ')
        })
      })
      // 加载图表
      this.loadDiagram()
    },
    genGrafana() {
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
