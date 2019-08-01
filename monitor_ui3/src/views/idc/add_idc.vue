<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="机房名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入机房名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="楼层">
        <el-col :span="8">
          <el-input v-model="form.floor" placeholder="请输入楼层"/>
        </el-col>
      </el-form-item>
      <el-form-item label="备注">
        <el-col :span="8">
          <el-input v-model="form.desc" placeholder="请输入备注"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            addIdc(form.name, form.floor, form.desc)
          "
        >创建
        </el-button
        >
        <el-button @click="jumpIdcList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_idc } from '../../api/idc'

export default {
  name: 'AddIdc',
  data() {
    return {
      form: {
        name: '',
        idc: '',
        floor: '',
        desc: ''
      }
    }
  },
  methods: {
    addIdc(a, b, c) {
      add_idc(a, b, c)
    },
    jumpIdcList() {
      this.$router.push({ path: '/idc_list' })
    }
  }
}
</script>

<style scoped>

</style>
