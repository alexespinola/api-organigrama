
@extends('layouts.app')
@section('header_title', 'Edición de usuarios')
@section('header_subtitle', ': Areas del usuario.')
@section('camino')
  <li class="breadcrumb-item active"> <i class="fa fa-users"></i> Areas del usuario</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="card card-primary">
        <div class="card-header with-border">
          <h3 class="card-title">Configuración</h3>
          <div class="card-tools pull-right">
            {{-- <a href="{{url('roles/create')}}"  class="btn btn-success btn-xs"  title=""><i class="fa fa-plus"></i> Agregar config</a> --}}
          </div>
        </div>
        <div class="card-body" id="app">
          <p>Aquí se define a que niveles del arbol del organigrama SOFSE puede acceder el usuario.</p>
          @{{test}}
        </div>
      </div>
    </div>
  </div>
@endsection

@push('page_scripts')
  <script src="{{asset('js/vue.js')}}"></script>
  <script>
    const appUrl = "{{ url('/'); }}";

    const { createApp } = Vue;
    createApp ({
      data() {
        return {
          test: 'VUE is work!',
          levelsTypes:[],
          arbol:[],
        }
      },
      methods: {
        getLevelsTypes(){
          let self = this;
          $.ajax({
            type: "GET",
            url: appUrl + '/api-organigrama-get-levels-types',
            dataType: "json",
            success: function (response) {
              self.levelsTypes = response;
            }
          });
        },
        getLeavesByParent(parentId, deep){
          let self = this;
          $.ajax({
            type: "GET",
            url: appUrl + '/api-organigrama-get-leaves-by-parent',
            dataType: "json",
            data: {parentId:parentId , deep:deep},
            success: function (response) {
              self.arbol = response;
            }
          });
        }
      },
      mounted() {
        this.getLevelsTypes();
        this.getLeavesByParent(1,1);
      }
    }).mount('#app')
  </script>
@endpush
