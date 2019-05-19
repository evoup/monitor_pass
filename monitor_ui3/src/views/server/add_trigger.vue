<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="主机名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入hostname" />
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { add_trigger } from '../../api/trigger'
export default {
  data() {
    return {
      form: {
        name: '',
        expression: ''
      }
    }
  },
  methods: {
    onSubmit() {
      add_trigger('a', 'b')
      this.$message('submit!')
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning'
      })
    },
    add_trigger(a, b, c, d, e) {
      add_trigger(a, b, c, d, e)
    }
  }
}
</script>

<style scoped></style>
