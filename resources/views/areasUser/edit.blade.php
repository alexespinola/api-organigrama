
@extends('layouts.app')
@section('header_title', 'Areas del usuario')
@section('header_subtitle', ': Defina a que áreas pertenece o puede acceder el usuario.')
@section('camino')
  <li class="breadcrumb-item active"> <i class="fa fa-users"></i> Áreas del usuario</li>
@endsection

@section('content')
  <div class="row" id="app">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="card card-primary">
        <div class="card-header with-border">
          <h3 class="card-title">Seleccione las áreas para el usuario: {{$user->name}}</h3>
          <div class="card-tools pull-right">
            <span @click="saveAreasUser" class="btn btn-success btn-xs">Guardar configuracíon</span>
          </div>
        </div>
        <div class="card-body" >
          <div class="row">
            {{-- Arbol del sistema --}}
            <div class="col-12" style="max-height: 500px; overflow-y: auto;">
              <h5 class="pl-2">Areas disponibles</h5> <hr>
              <div v-if="customTree" v-for="root in customTree">
                <node-component
                  class="pl-5"
                  :node="root">
                </node-component>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection


@push('page_scripts')
  <script src="{{asset('js/vue.js')}}"></script>
  <script>
    const appUrl = "{{ url('/'); }}";
    let storedConfig = JSON.parse(`{!! $storedConfig !!}`);
    let areasUser = JSON.parse(`{!! $areasUser !!}`);

    //componente nodo
    const NodeComponent = {
      props: ['node'],
      data: function () {
        return {
          checked: this.node.selected,
        }
      },
      methods: {
        selectNode(){
          this.node.selected = !this.checked;
        }
      },
      template: `
      <div style="border-left: solid 1px !important;  border-left-color: #3f6791 !important;">
        <span :id="node.id" >
          <input type="checkbox" v-model="checked" @click="selectNode()">
          @{{node.nombre}}
        </span>

        <node-component
          class="pl-5"
          v-if="node.children && node.children.length"
          v-for="child in node.children"
          :node="child">
        </node-component>
      </div>
      `
    };

    // isntancia de VUE
    const { createApp } = Vue;
    const app = createApp ({
      data() {
        return {
          id_user: `{!! $user->id !!}`,
          customTree: null,
          customNodes: [],
          relacionesNiveles: [],
          levelsTypes: [],
        }
      },
      methods: {
        getCustomTreeNodes(elem=null, result=[], level=0){
          if(elem instanceof Array) {
            for(var i = 0; i < elem.length; i++) {
              this.getCustomTreeNodes(elem[i], result, level+1);
            }
          }
          else
          {
            if(elem.hasOwnProperty('selected') && elem.selected ){
              result.push({
                id: elem.id,
                nombre: elem.nombre,
                descripcion: elem.descripcion,
                selected: elem.selected,
                tipo_id: elem.tipo_id,
                parent_id: elem.parent_id? elem.parent_id: 0,
                level: level+1,
              });
            }

            if(elem.hasOwnProperty('children') && elem.children instanceof Array && elem.children.length){
              this.getCustomTreeNodes(elem.children, result, level+1);
            }
          }
          return result;
        },
        getCustomTree(parent){
          let children = this.customNodes.filter(e=> e.parent_id == parent.id);
          if(children) {
            parent.children = [];
            for (const child of children) {
              let childData = {
                id: child.id,
                nombre: child.nombre,
                descripcion: child.descripcion,
                tipo_id: child.tipo_id,
                tipo: child.tipo,
                parent_id: child.parent_id
              }
              parent.children.push(childData);
              this.getCustomTree(childData);
            }
          }
          return parent;
        },
        async saveAreasUser(){
          if(!this.customTree) return false;
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          try{
            const response = await $.ajax({
              type: "PUT",
              url: appUrl + '/areas-user/'+this.id_user,
              data: {tree: JSON.stringify(this.customTree)},
              dataType: "json"
            });
            Swal.fire('Bien!', 'configuración guardada.' , 'success');
            window.location.href= appUrl+"/users/"+this.id_user+"/edit";
          }
          catch(ex){
            console.error('Error al guardar: ',ex.responseJSON.error);
            Swal.fire('Error al guardar', ex.responseJSON.error , 'error');
          }
        }
      },
      async mounted() {
        if(areasUser.length)
          this.customTree = areasUser;
        else
          this.customTree = storedConfig;
      }
    })
    .component("node-component", NodeComponent)
    .mount('#app')

  </script>
@endpush
