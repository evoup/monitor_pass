<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <el-tab-pane label="基本信息" class="base-info">
        <el-row>
          <el-col>
            主机名： {{ form.host_name }}
          </el-col>
        </el-row>
        <el-row>
          <el-col>
            序列号： {{ form.serial_number }}
          </el-col>
        </el-row>
        <el-row>
          <el-col>
            IP： {{ form.ip }}
          </el-col>
        </el-row>
        <el-row>
          <el-col>
            状态： {{ form.status }}
          </el-col>
        </el-row>
        <el-row>
          <el-col>
            更新时间： {{ form.update_time }}
          </el-col>
        </el-row>
        <el-row>
          <el-col>
            机房： {{ form.idc }}
          </el-col>
          <el-row>
            <el-col>
              楼层： {{ form.floor }}
            </el-col>
          </el-row>
        </el-row>
        <el-row>
          <el-col>
            机柜： {{ form.carinet }}
          </el-col>
        </el-row>
        <el-row>
          <el-col>
            柜上位置： {{ form.position }}
          </el-col>
        </el-row>
        <el-row>
          <el-col>
            业务线： {{ form.business_line }}
          </el-col>
        </el-row>
      </el-tab-pane>
      <el-tab-pane label="硬件信息">
        <table class="guige-table" border="1">
          <tbody>
            <tr>
              <th colspan="6" style="border-left:2px solid #0488cd;"><span class="padding-l-8">基础信息</span></th>
            </tr>
            <tr>
              <td>名称</td>
              <td colspan="5">值</td>
            </tr>
            <tr>
              <td>操作系统</td>
              <td colspan="5">{{ form.os_platform }}</td>
            </tr>
            <tr>
              <td>系统版本</td>
              <td colspan="5">{{ form.os_version }}</td>
            </tr>
            <tr>
              <td>主板序列号</td>
              <td colspan="5">{{ form.serial_number }}</td>
            </tr>
            <tr>
              <td>主板型号</td>
              <td colspan="5">{{ form.model }}</td>
            </tr>
            <tr>
              <td>主板厂商</td>
              <td colspan="5">{{ form.manufacturer }}</td>
            </tr>
            <tr>
              <td>CPU逻辑核数</td>
              <td colspan="5">{{ form.cpu_count }}</td>
            </tr>
            <tr>
              <td>CPU物理核数</td>
              <td colspan="5">{{ form.cpu_count }}</td>
            </tr>
            <tr>
              <td>CPU型号</td>
              <td colspan="5">{{ form.cpu_model }}</td>
            </tr>

            <!-- 网卡信息开始 -->
            <tr>
              <th colspan="6" class="title f14" style="border-left:2px solid #0488cd;"><span class="padding-l-8">网卡信息</span></th>
            </tr>
            <tr>
              <td>名称</td>
              <td>MAC地址</td>
              <td>IP地址</td>
              <td>掩码</td>
              <td colspan="2">UP</td>
            </tr>
            <tr v-for="(item, index) in form.nics" :key="'B'+ index">
              <td>{{ item.name }}</td>
              <td>{{ item.hwaddr }}</td>
              <td>{{ item.ip }}</td>
              <td>{{ item.netmask }}</td>
              <td v-if="item.up==true" colspan="2">是</td>
              <td v-if="item.up==false" colspan="2">否</td>
            </tr>
            <!-- 网卡信息结束 -->
            <!-- 硬盘信息开始 -->
            <tr>
              <th colspan="6" class="title f14" style="border-left:2px solid #0488cd;">
                <span class="padding-l-8">硬盘信息</span>
              </th>
            </tr>
            <tr>
              <td>插槽</td>
              <td>容量</td>
              <td>接口类型</td>
              <td colspan="3">型号</td>
            </tr>
            <tr v-for="(item, index) in form.disks" :key="'A'+ index">
              <td>{{ item.slot }}</td>
              <td>{{ item.size }}G</td>
              <td>{{ item.pd_type }}</td>
              <td colspan="3">{{ item.model }}</td>
            </tr>
            <!--<tr>-->
            <!--<td>1</td>-->
            <!--<td>279.396</td>-->
            <!--<td>SAS</td>-->
            <!--<td colspan="2">希捷ST300MM0006 LS08S0K2B5AH</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>5</td>-->
            <!--<td>476.939</td>-->
            <!--<td>SATA</td>-->
            <!--<td colspan="2">S1AXNSAFB00549A三星SSD 840 PRO系列DXM06B0Q</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>2</td>-->
            <!--<td>476.939</td>-->
            <!--<td>SATA</td>-->
            <!--<td colspan="2">S1SZNSAFA01085L三星SSD 850 PRO 512GB EXM01B6Q</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>3</td>-->
            <!--<td>476.939</td>-->
            <!--<td>SATA</td>-->
            <!--<td colspan="2">S1AXNSAF912433K三星SSD 840 PRO系列DXM06B0Q</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>0</td>-->
            <!--<td>279.396</td>-->
            <!--<td>SAS</td>-->
            <!--<td colspan="2">希捷ST300MM0006 LS08S0K2B5NV</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>4</td>-->
            <!--<td>476.939</td>-->
            <!--<td>SATA</td>-->
            <!--<td colspan="2">S1AXNSAF303909M三星SSD 840 PRO系列DXM05B0Q</td>-->
            <!--</tr>-->
            <!-- 硬盘信息结束 -->
            <!-- 内存信息开始 -->
            <tr>
              <th colspan="6" class="title f14" style="border-left:2px solid #0488cd;">
                <span class="padding-l-8">内存信息</span>
              </th>
            </tr>
            <tr>
              <td>插槽</td>
              <td>容量</td>
              <td>频率</td>
              <td>型号</td>
              <td>制造商</td>
              <td>sn</td>
            </tr>
            <tr v-for="(item, index) in form.memories" :key="index">
              <td>{{ item.slot }}</td>
              <td>{{ item.size }}G</td>
              <td>{{ item.speed }}</td>
              <td>{{ item.model }}</td>
              <td>{{ item.manufacturer }}</td>
              <td>{{ item.serial_number }}</td>

            </tr>
            <!--<tr>-->
            <!--<td>DIMM＃2</td>-->
            <!--<td>0.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>DIMM＃5</td>-->
            <!--<td>0.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>DIMM＃7</td>-->
            <!--<td>0.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>DIMM＃3</td>-->
            <!--<td>0.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>DIMM＃6</td>-->
            <!--<td>0.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>DIMM＃4</td>-->
            <!--<td>0.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>DIMM＃0</td>-->
            <!--<td>1024.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
            <!--<tr>-->
            <!--<td>DIMM＃1</td>-->
            <!--<td>0.0</td>-->
            <!--<td>667 MHz</td>-->
            <!--<td colspan="2">DRAM</td>-->
            <!--</tr>-->
          <!-- 内存信息结束 -->

          </tbody>
        </table>
      </el-tab-pane>
      <el-tab-pane label="变更记录">
        <el-timeline>
          <el-timeline-item
            v-for="(activity, index) in form.activities"
            :key="index"
            :timestamp="activity.create_at">
            <el-card>
              <h4>{{ activity.content }}</h4>
              <p>自动采集</p>
            </el-card>
          </el-timeline-item>
        </el-timeline>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import { asset_record_list, read_asset } from '../../api/asset'

export default {
  name: 'AssetDetail',
  data() {
    return {
      form: {
        host_name: null,
        serial_number: null,
        ip: '172.12.11.3',
        status: null,
        update_time: '2019-07-17',
        idc: null,
        carinet: '12s',
        floor: '12f',
        position: '5',
        business_line: '广告投放',
        os_platform: null,
        os_version: null,
        model: null,
        manufacturer: null,
        cpu_count: null,
        cpu_physical_count: null,
        cpu_model: null,
        // nic_name: null,
        // nic_hwaddr: null,
        // nic_ip: null,
        // nic_netmask: null,
        nics: [],
        disks: [],
        memories: [],
        activities: []
      },
      pageHelp: {
        page: 1,
        size: 99999,
        order: 'asc'
      },
      sortHelp: {
        prop: 'create_at',
        order: 'descending'
      }
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      read_asset({ id: this.$route.query.asset_id }).then(response => {
        this.form.host_name = response.data.item.host_name
        this.form.idc = response.data.item.idc.name
        this.form.serial_number = response.data.item.server.serial_number
        if (response.data.item.device_status_id === 1) {
          this.form.status = '上架'
        }
        if (response.data.item.device_status_id === 2) {
          this.form.status = '在线'
        }
        if (response.data.item.device_status_id === 3) {
          this.form.status = '离线'
        }
        if (response.data.item.device_status_id === 4) {
          this.form.status = '下架'
        }
        this.form.os_platform = response.data.item.server.os_platform
        this.form.os_version = response.data.item.server.os_version
        this.form.model = response.data.item.server.model
        this.form.manufacturer = response.data.item.server.manufacturer
        this.form.cpu_count = response.data.item.server.cpu_count
        this.form.cpu_physical_count = response.data.item.server.cpu_physical_count
        this.form.cpu_model = response.data.item.server.cpu_model
        this.form.nics = response.data.item.server.nics
        // this.form.nic_name = response.data.item.server.nic_name
        // this.form.nic_hwaddr = response.data.item.server.nic_hwaddr
        // this.form.nic_ip = response.data.item.server.nic_ip
        // this.form.nic_netmask = response.data.item.server.nic_netmask
        this.form.disks = response.data.item.server.disks
        this.form.memories = response.data.item.server.memories
        this.read_single_asset_record()
      })
    },
    read_single_asset_record() {
      asset_record_list(Object.assign(this.pageHelp, this.sortHelp, { asset_obj: this.$route.query.id })).then(response => {
        this.form.activities = response.data.items
      })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ .base-info {
    line-height: 30px;
    font-size: 14px;
  }
  .app-container /deep/ table {
    font-size: 14px;
    text-align: left;
    width:100%;
    line-height: 30px;
    height: 30px;
    border-collapse: collapse;
    border: 1px solid #dcdcdc;
  }
  .app-container /deep/ table th {
    padding:0 10px;
    background-color: #f5f7fa;
  }
  .app-container /deep/ table td {
    padding:0 10px;
    border: 1px solid #dcdcdc;
  }
  .app-container /deep/ .el-card h4 {
    font-weight: normal;
  }
</style>
