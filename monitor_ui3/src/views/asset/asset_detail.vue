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
        <table class="guige-table">
          <tbody>
            <tr>
              <th colspan="5" style="border-left:2px solid #0488cd;"><span class="padding-l-8">基础信息</span></th>
            </tr>
            <tr>
              <td>名称</td>
              <td colspan="4">值</td>
            </tr>
            <tr>
              <td>操作系统</td>
              <td colspan="4">Linux的</td>
            </tr>
            <tr>
              <td>系统版本</td>
              <td colspan="4">CentOS 6.6版（最终版）</td>
            </tr>
            <tr>
              <td>主板序列号</td>
              <td colspan="4">Parallels-1A 1B CB 3B 64 66 4B 13 86 B0 86 FF 7E 2B 20 30</td>
            </tr>
            <tr>
              <td>主板型号</td>
              <td colspan="4">Parallels虚拟平台</td>
            </tr>
            <tr>
              <td>主板厂商</td>
              <td colspan="4">Parallels Software International Inc</td>
            </tr>
            <tr>
              <td>CPU逻辑核数</td>
              <td colspan="4">24</td>
            </tr>
            <tr>
              <td>CPU物理核数</td>
              <td colspan="4">2</td>
            </tr>
            <tr>
              <td>CPU型号</td>
              <td colspan="4">英特尔（R）Xeon（R）CPU E5-2620 v2 @ 2.10GHz</td>
            </tr>

            <!-- 网卡信息开始 -->
            <tr>
              <th colspan="5" class="title f14" style="border-left:2px solid #0488cd;"><span class="padding-l-8">网卡信息</span></th>
            </tr>
            <tr>
              <td>名称</td>
              <td>MAC地址</td>
              <td>IP地址</td>
              <td>掩码</td>
              <td>UP</td>
            </tr>
            <tr>
              <td>为eth0</td>
              <td>00：1c：42：a5：57：7a</td>
              <td>10.211.55.4</td>
              <td>255.255.255.0</td>
              <td>是</td>
            </tr>
            <!-- 网卡信息结束 -->
            <!-- 硬盘信息开始 -->
            <tr>
              <th colspan="5" class="title f14" style="border-left:2px solid #0488cd;">
                <span class="padding-l-8">硬盘信息</span>
              </th>
            </tr>
            <tr>
              <td>插槽</td>
              <td>容量</td>
              <td>接口类型</td>
              <td colspan="2">型号</td>
            </tr>
            <tr>
              <td>1</td>
              <td>279.396</td>
              <td>SAS</td>
              <td colspan="2">希捷ST300MM0006 LS08S0K2B5AH</td>
            </tr>
            <tr>
              <td>5</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1AXNSAFB00549A三星SSD 840 PRO系列DXM06B0Q</td>
            </tr>
            <tr>
              <td>2</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1SZNSAFA01085L三星SSD 850 PRO 512GB EXM01B6Q</td>
            </tr>
            <tr>
              <td>3</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1AXNSAF912433K三星SSD 840 PRO系列DXM06B0Q</td>
            </tr>
            <tr>
              <td>0</td>
              <td>279.396</td>
              <td>SAS</td>
              <td colspan="2">希捷ST300MM0006 LS08S0K2B5NV</td>
            </tr>
            <tr>
              <td>4</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1AXNSAF303909M三星SSD 840 PRO系列DXM05B0Q</td>
            </tr>
            <!-- 硬盘信息结束 -->
            <!-- 内存信息开始 -->
            <tr>
              <th colspan="5" class="title f14" style="border-left:2px solid #0488cd;">
                <span class="padding-l-8">内存信息</span>
              </th>
            </tr>
            <tr>
              <td>插槽</td>
              <td>容量</td>
              <td>频率</td>
              <td colspan="2">型号</td>
            </tr>
            <tr>
              <td>DIMM＃2</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃5</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃7</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃3</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃6</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃4</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃0</td>
              <td>1024.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃1</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
          <!-- 内存信息结束 -->

          </tbody>
        </table>
      </el-tab-pane>
      <el-tab-pane label="变更记录">
        <el-timeline>
          <el-timeline-item
            v-for="(activity, index) in activities"
            :key="index"
            :timestamp="activity.timestamp">
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
import { read_asset } from '../../api/asset'

export default {
  name: 'AssetDetail',
  created() {
    this.fetchData()
  },
  data() {
    return {
      form: {
        host_name: null,
        serial_number: 'Parallels-1A 1B CB 3B 64 66 4B 13 86 B0 86 FF 7E 2B 20 30',
        'ip': '172.12.11.3',
        'status': '上架',
        'update_time': '2019-07-17',
        'idc': null,
        'carinet': '12s',
        'floor': '12f',
        'position': '5',
        'business_line': '广告投放'
      },
      activities: [{
        content: '[新增硬盘]插槽为4;容量为476.939;硬盘类型为SATA;型号为S1AXNSAF303909M Samsung SSD 840 PRO Series DXM05B0Q',
        timestamp: '2019-04-15'
      }, {
        content: '[新增网卡]eth0:mac地址为00:1c:42:a5:57:7a;状态为True;掩码为255.255.255.0;IP地址为10.211.55.4',
        timestamp: '2019-04-13'
      }, {
        content: '系统由变更为linux;系统版本由变更为CentOS release 6.6 (Final);主板SN号由asdfasdfasdfasdf变更为Parallels-1A 1B CB 3B 64 66 4B 13 86 B0 86 FF 7E 2B 20 30;主板厂商由变更为Parallels Software International Inc.;主板型号由变更为Parallels Virtual Platform;CPU逻辑核数由None变更为24;CPU物理核数由None变更为2;CPU型号由变更为 Intel(R) Xeon(R) CPU E5-2620 v2 @ 2.10GHz',
        timestamp: '2019-04-11'
      }]
    }
  },
  methods: {
    fetchData() {
      read_asset({id: this.$route.query.asset_id}).then(response => {
        this.form.host_name = response.data.item.host_name
        this.form.idc = response.data.item.idc.name
        console.log(response)
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
